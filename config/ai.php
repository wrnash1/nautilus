<?php
/**
 * AI Configuration
 * 
 * Settings for AI/ML features
 */

return [
    // Enable AI features
    'enabled' => env('AI_ENABLED', true),
    
    // Contribute to community learning (Phase 2)
    'contribute_to_community' => env('AI_CONTRIBUTE', false),
    
    // Privacy settings
    'differential_privacy' => env('AI_PRIVACY', true),
    'anonymize_contributions' => true,
    
    // Model settings
    'forecast_days_ahead' => 30,
    'forecast_history_days' => 90,
    'reorder_lead_time_days' => 7,
    'safety_stock_days' => 3,
    
    // Recommendation limits
    'max_recommendations' => 50,
    'top_products_limit' => 20,
    
    // Training schedule
    'retrain_frequency' => 'daily', // daily, weekly, monthly
    'retrain_time' => '02:00', // 2 AM
    
    // Model versioning
    'model_version' => '1.0.0',
    'model_storage_path' => storage_path('ai/models'),
    
    // GitHub sync (Phase 2)
    'github_repo' => 'nautilus-ai-models',
    'github_enabled' => false,
    
    // Accuracy thresholds
    'min_accuracy' => 0.70,
    'min_data_points' => 7,
    
    // Caching
    'cache_forecasts' => true,
    'cache_ttl' => 3600, // 1 hour
];
