<?php

return [
    'app_name' => env('DOXA_APP_NAME', 'Doxa Business Finder'),
    'company' => env('DOXA_COMPANY', 'Doxa Infotech Pvt. Ltd.'),
    
    // Credit costs (configurable)
    'credits' => [
        'search_cost' => (int) env('DOXA_SEARCH_CREDIT_COST', 5),
        'detail_cost' => (int) env('DOXA_DETAIL_CREDIT_COST', 2),
        'ai_analysis_cost' => (int) env('DOXA_AI_CREDIT_COST', 10),
        'enrichment_cost' => (int) env('DOXA_ENRICHMENT_CREDIT_COST', 3),
        'low_credit_threshold' => (int) env('DOXA_LOW_CREDIT_THRESHOLD', 20),
    ],
    
    // Opportunity Score weights (must sum to 100)
    'opportunity_score' => [
        'website_opportunity' => 30,
        'digital_presence_gap' => 20,
        'business_relevance' => 15,
        'business_activity' => 15,
        'contact_availability' => 10,
        'growth_potential' => 10,
    ],
    
    // Google Places API
    'google_places' => [
        'api_key' => env('GOOGLE_PLACES_API_KEY'),
        'base_url' => 'https://places.googleapis.com/v1/places',
        'max_results' => (int) env('GOOGLE_PLACES_MAX_RESULTS', 20),
        'cache_ttl' => (int) env('GOOGLE_PLACES_CACHE_TTL', 86400), // 24 hours
        'estimated_cost_per_search' => 0.032, // configurable
        'estimated_cost_per_detail' => 0.017,
    ],
    
    // Google Gemini AI Lead Analyzer
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    ],
    
    // Payment gateway
    'payment' => [
        'gateway' => env('PAYMENT_GATEWAY', 'razorpay'),
        'razorpay' => [
            'key_id' => env('RAZORPAY_KEY_ID'),
            'key_secret' => env('RAZORPAY_KEY_SECRET'),
            'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
        ],
    ],
    
    // Services offered by Doxa
    'services' => [
        'website' => 'Website Development',
        'ecommerce' => 'E-Commerce',
        'mobile_app' => 'Mobile App',
        'digital_marketing' => 'Digital Marketing',
        'branding' => 'Branding',
        'video_creation' => 'Video Creation',
        'crm' => 'CRM',
        'custom_software' => 'Custom Software',
    ],
    
    // Subscription settings
    'subscription' => [
        'trial_days' => (int) env('DOXA_TRIAL_DAYS', 14),
        'grace_period_days' => (int) env('DOXA_GRACE_DAYS', 3),
        'expiry_reminders' => [7, 3, 1], // days before expiry
    ],
    
    // Security
    'security' => [
        'max_login_attempts' => 5,
        'lockout_minutes' => 15,
        'api_rate_limit' => 60, // per minute
        'search_rate_limit' => 30, // per minute
    ],
];
