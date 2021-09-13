<?php

namespace Chuckcms\Contacts\Traits;

use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;
use Chuckcms\Contacts\Contracts\Contact;
use Chuckcms\Contacts\Exceptions\FailedValidation;
use Chuckcms\Contacts\Models\Contact as ContactModel;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasContacts
{
    private $contactClass;

    /**
     * Boot the contactable trait for the model.
     *
     * @return void
     */
    public static function bootHasContacts()
    {
        static::deleting(function (self $model) {
            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                $model->contacts()->forceDelete();

                return;
            }

            $model->contacts()->delete();
        });
    }

    public function getContactClass()
    {
        if (!isset($this->contactClass)) {
            $this->contactClass = config('contacts.models.contact');
        }

        return $this->contactClass;
    }

    /**
     * A model may have multiple contacts.
     *
     * @return MorphToMany
     */
    public function contacts(): MorphToMany
    {
        return $this->morphToMany(
            config('contacts.models.contact'),
            'model',
            config('contacts.table_names.model_has_contacs'),
            config('contacts.column_names.model_morph_key'),
            'contact_id'
        );
    }

    /**
     * Assign the given contacts to the model. (credit to Spatie)
     *
     * @param array|int|\Chuckcms\Contacts\Contracts\Contact ...$contacts
     *
     * @return $this
     */
    public function assignContact(...$contacts)
    {
        $contacts = collect($contacts)
            ->flatten()
            ->map(function ($contact) {
                if (empty($contact)) {
                    return false;
                }

                return $this->getStoredContact($contact);
            })
            ->filter(function ($contact) {
                return $contact instanceof Contact;
            })
            ->map->id
            ->all();

        $model = $this->getModel();

        if ($model->exists) {
            $this->contacts()->sync($contacts, false);
            $model->load('contacts');
        } else {
            $class = \get_class($model);

            $class::saved(
                function ($object) use ($roles, $model) {
                    static $modelLastFiredOn;
                    if ($modelLastFiredOn !== null && $modelLastFiredOn === $model) {
                        return;
                    }
                    $object->contacts()->sync($roles, false);
                    $object->load('contacts');
                    $modelLastFiredOn = $object;
                });
        }

        return $this;
    }

    /**
     * Revoke the given contact from the model.
     *
     * @param int|\Chuckcms\Contacts\Contracts\Contact $contact
     */
    public function removeContact($contact)
    {
        $this->contacts()->detach($this->getStoredContact($contact));

        $this->load('contacts');

        return $this;
    }

    /**
     * Remove all current contacts and set the given ones.
     *
     * @param array|int|\Chuckcms\Contacts\Contracts\Contact ...$contacts
     *
     * @return $this
     */
    public function syncContacts(...$contacts)
    {
        $this->contacts()->detach();

        return $this->assignContact($contacts);
    }

    /**
     * Check if model has contacts.
     *
     * @return bool
     */
    public function hasContacts(): bool
    {
        return (bool) count($this->contacts);
    }

    /**
     * Add a contact to this model.
     *
     * @param array $attributes
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function addContact(array $attributes)
    {
        $attributes = $this->loadContactAttributes($attributes);

        return $this->contacts()->create($attributes);
    }

    /**
     * Updates the given contact.
     *
     * @param Contact $contact
     * @param array   $attributes
     *
     * @throws Exception
     *
     * @return bool
     */
    public function updateContact(Contact $contact, array $attributes): bool
    {
        $attributes = $this->loadContactAttributes($attributes);

        return $contact->fill($attributes)->save();
    }

    /**
     * Deletes given contact(s).
     *
     * @param int|array|\Chuckcms\Contacts\Contracts\Contact $contacts
     * @param bool                                           $force
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function deleteContact($contacts, $force = false): bool
    {
        if (is_int($contacts) && $this->hasContact($contacts)) {
            return $force ?
                    $this->contacts()->where('id', $contacts)->forceDelete() :
                    $this->contacts()->where('id', $contacts)->delete();
        }

        if ($contacts instanceof Contact && $this->hasContact($contacts)) {
            return $force ?
                    $this->contacts()->where('id', $contacts->id)->forceDelete() :
                    $this->contacts()->where('id', $contacts->id)->delete();
        }

        if (is_array($contacts)) {
            foreach ($contacts as $contact) {
                if ($this->deleteContact($contact, $force)) {
                    continue;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Forcefully deletes given contact(s).
     *
     * @param int|array|\Chuck\Contact\Contracts\Contact $contacts
     * @param bool                                       $force
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function forceDeleteContact($contacts): bool
    {
        return $this->deleteContact($contacts, true);
    }

    /**
     * Determine if the model has (one of) the given contact(s).
     *
     * @param int|array|\Chuck\Contact\Contracts\Contact|\Illuminate\Support\Collection $contacts
     *
     * @return bool
     */
    public function hasContact($contacts): bool
    {
        if (is_int($contacts)) {
            return $this->contacts->contains('id', $contacts);
        }

        if ($contacts instanceof Contact) {
            return $this->contacts->contains('id', $contacts->id);
        }

        if (is_array($contacts)) {
            foreach ($contacts as $contact) {
                if ($this->hasContact($contact)) {
                    return true;
                }
            }

            return false;
        }

        return $contacts->intersect($this->contacts)->isNotEmpty();
    }

    public function getContactFirstNames(): Collection
    {
        return $this->contacts->pluck('first_name');
    }

    /**
     * Get the public contact.
     *
     * @param string $direction
     *
     * @return Contact|null
     */
    public function getPublicContact(string $direction = 'desc'): ?Contact
    {
        return $this->contacts()
                    ->isPublic()
                    ->orderBy('is_public', $direction)
                    ->first();
    }

    /**
     * Get the primary contact.
     *
     * @param string $direction
     *
     * @return Contact|null
     */
    public function getPrimaryContact(string $direction = 'desc'): ?Contact
    {
        return $this->contacts()
                    ->isPrimary()
                    ->orderBy('is_primary', $direction)
                    ->first();
    }

    /**
     * Load and validate the contact attributes array.
     *
     * @param array $attributes
     *
     * @throws FailedValidation
     *
     * @return array
     */
    public function loadContactAttributes(array $attributes): array
    {
        if (!isset($attributes['first_name'])) {
            throw new FailedValidation('[Contacts] No first name given.');
        }

        $validator = $this->validateContact($attributes);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $error = '[Contacts] '.implode(' ', $errors);

            throw new FailedValidation($error);
        }

        return $attributes;
    }

    /**
     * Validate the contact.
     *
     * @param array $attributes
     *
     * @return Validator
     */
    public function validateContact(array $attributes): Validator
    {
        $rules = (new ContactModel())->getValidationRules();

        return validator($attributes, $rules);
    }

    protected function getStoredContact($contact): Contact
    {
        $contactClass = $this->getContactClass();

        if (is_numeric($contact)) {
            return $contactClass->findById($contact);
        }

        return $contact;
    }
}
