<?php

return [
    'default_items' => [
        'Administrative' => [
            ['id' => 'signed_agreement', 'label' => 'Signed Service Agreement', 'description' => 'Legal document signed by both parties.'],
            ['id' => 'onboarding_fee', 'label' => 'Onboarding Fee Paid', 'description' => 'Initial setup and strategic planning fee.'],
            ['id' => 'billing_details', 'label' => 'Billing Profiles Configured', 'description' => 'Credit card or automated payment method set up.'],
        ],
        'Access & Integrations' => [
            ['id' => 'fb_access', 'label' => 'Facebook Ad Account Access', 'description' => 'Agency partner access granted via Business Manager.'],
            ['id' => 'ga_access', 'label' => 'Google Analytics Access', 'description' => 'Property edit access for conversion tracking.'],
            ['id' => 'crm_connect', 'label' => 'CRM Connection Established', 'description' => 'API or Webhook link for lead transmission.'],
        ],
        'Brand & Strategy' => [
            ['id' => 'vector_logos', 'label' => 'Vector Logos Provided', 'description' => 'SVG or AI files for high-quality production.'],
            ['id' => 'brand_guide', 'label' => 'Brand Style Guide', 'description' => 'Color palettes, fonts, and usage rules.'],
            ['id' => 'primary_goals', 'label' => 'Primary KPIs Defined', 'description' => 'Specific conversion targets for the coming quarter.'],
        ],
    ],
];
