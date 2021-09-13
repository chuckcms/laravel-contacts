<?php

return [

    'models' => [

        /*
         * Which model to use for the Contact when using the 'HasContacts' trait.
         *
         */

        'contact' => Chuckcms\Contacts\Models\Contact::class,

        /*
         * Define the table name to use when using the 'HasContacts' trait for
         * retrieving the models linked to the contacts.
         */

        'model_has_contacts' => 'model_has_contacts',

    ],

    'table_names' => [

        /*
         * Define the table name to use when using the 'HasContacts' trait.
         */

        'contacts' => 'contacts',

    ],

    'column_names' => [

        'model_morph_key' => 'model_id',

    ],

    'fields' => [
        'contacts' => [
            'type'          => 'nullable|string|max:20',
            'gender'        => 'nullable|string|max:1',

            'first_name'    => 'required|string|max:60',
            'middle_name'   => 'nullable|string|max:60',
            'last_name'     => 'nullable|string|max:60',
            
            'phone'         => 'nullable|string|max:32',
            'mobile'        => 'nullable|string|max:32',
            'fax'           => 'nullable|string|max:32',
            'email'         => 'nullable|string|max:90',
            'website'       => 'nullable|string|max:140',

            'is_public'     => 'sometimes|boolean',
            'is_primary'    => 'sometimes|boolean'
        ],
    ],
];
