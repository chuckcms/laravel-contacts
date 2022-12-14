<?php

namespace Chuckcms\Contacts\Models;

use Chuckcms\Addresses\Traits\HasAddresses;
use Chuckcms\Contacts\Contracts\Contact as ContactContract;
use Chuckcms\Contacts\Exceptions\ContactDoesNotExist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model implements ContactContract
{
    use HasAddresses, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * The attributes that are fillable on this model.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The default rules that the model will validate against.
     *
     * @var array
     */
    protected $rules = [];

    public function __construct(array $attributes = [])
    {
        $this->setTable(config('contacts.table_names.contacts'));
        $this->mergeFillables();

        parent::__construct($attributes);
    }

    /**
     * Merge fillable fields.
     *
     * @return void.
     */
    private function mergeFillables()
    {
        $fillable = $this->fillable;
        $columns = array_keys(config('contacts.fields.contacts'));

        $this->fillable(array_merge($fillable, $columns));
    }

    /**
     * Get the validation rules.
     *
     * @return array
     */
    public static function getValidationRules(): array
    {
        $rules = config('contacts.fields.contacts');

        return $rules;
    }

    /**
     * Scope public contacts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsPublic(Builder $builder): Builder
    {
        return $builder->where('is_public', true);
    }

    /**
     * Scope primary contacts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsPrimary(Builder $builder): Builder
    {
        return $builder->where('is_primary', true);
    }

    public static function findById(int $id): ContactContract
    {
        $contact = static::where('id', $id)->first();

        if (!$contact) {
            throw ContactDoesNotExist::withId($id);
        }

        return $contact;
    }
}
