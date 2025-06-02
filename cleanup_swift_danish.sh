#!/bin/bash

# Echo Server Conversion - Swift Danish Cleanup Script
# This script removes all Swift Danish specific files and components
# Review carefully before running!

# Exit on any error
set -e

echo "=== Swift Danish Cleanup Script ==="
echo "This script will remove Swift Danish specific files."
echo "It will NOT touch:"
echo "- Generic shadcn-vue components"
echo "- Core Laravel files"
echo "- Configuration files"
echo "- Package files"
echo ""
echo "Press Ctrl+C to cancel, or Enter to continue..."
read

# Create backup directory with timestamp
BACKUP_DIR="backup_$(date +%Y%m%d_%H%M%S)"
echo "Creating backup directory: $BACKUP_DIR"
mkdir -p "$BACKUP_DIR"

# Function to safely remove files/directories
safe_remove() {
    local path=$1
    if [ -e "$path" ]; then
        echo "Removing: $path"
        # Move to backup instead of deleting
        mkdir -p "$BACKUP_DIR/$(dirname "$path")"
        mv "$path" "$BACKUP_DIR/$path" 2>/dev/null || true
    else
        echo "Already removed or doesn't exist: $path"
    fi
}

echo ""
echo "=== Phase 1: Removing Swift Danish Controllers ==="
safe_remove "app/Http/Controllers/CategoryController.php"
safe_remove "app/Http/Controllers/GameController.php"
safe_remove "app/Http/Controllers/WordPairController.php"
safe_remove "app/Http/Controllers/ErrorTestingController.php"

echo ""
echo "=== Phase 2: Removing Authentication Controllers ==="
safe_remove "app/Http/Controllers/Auth"
safe_remove "app/Http/Controllers/Admin"
safe_remove "app/Http/Controllers/Settings"

echo ""
echo "=== Phase 3: Removing Models ==="
safe_remove "app/Models/Category.php"
safe_remove "app/Models/DifficultyLevel.php"
safe_remove "app/Models/WordPair.php"
# Keep User.php for now as it might be referenced

echo ""
echo "=== Phase 4: Removing Policies ==="
safe_remove "app/Policies/UserPolicy.php"
safe_remove "app/Policies/WordPairPolicy.php"

echo ""
echo "=== Phase 5: Removing Requests ==="
safe_remove "app/Http/Requests/Auth"
safe_remove "app/Http/Requests/Settings"
safe_remove "app/Http/Requests/StoreWordPairRequest.php"
safe_remove "app/Http/Requests/UpdateWordPairRequest.php"

echo ""
echo "=== Phase 6: Removing Middleware ==="
# Keep HandleInertiaRequests.php as it's core to Inertia
safe_remove "app/Http/Middleware/HandleAppearance.php"

echo ""
echo "=== Phase 7: Removing Database Files ==="
# Remove migrations except core Laravel ones
safe_remove "database/migrations/2025_05_30_113155_add_user_type_to_users_table.php"
safe_remove "database/migrations/2025_05_30_113159_create_difficulty_levels_table.php"
safe_remove "database/migrations/2025_05_30_113204_create_categories_table.php"
safe_remove "database/migrations/2025_05_30_113209_create_word_pairs_table.php"
safe_remove "database/migrations/2025_05_30_113214_create_category_word_pair_table.php"
safe_remove "database/migrations/2025_05_31_101557_add_level_to_difficulty_levels_table.php"

# Remove seeders except DatabaseSeeder
safe_remove "database/seeders/AdditionalCommonPhrasesSeeder.php"
safe_remove "database/seeders/AdditionalFoodDrinkSeeder.php"
safe_remove "database/seeders/AdditionalGreetingsSeeder.php"
safe_remove "database/seeders/AdditionalTimeDaysSeeder.php"
safe_remove "database/seeders/CategorySeeder.php"
safe_remove "database/seeders/ColorsSeeder.php"
safe_remove "database/seeders/DifficultyLevelSeeder.php"
safe_remove "database/seeders/FamilySeeder.php"
safe_remove "database/seeders/HealthLifeQualitySeeder.php"
safe_remove "database/seeders/HolidaysTraditionsSeeder.php"
safe_remove "database/seeders/SchoolEducationSeeder.php"
safe_remove "database/seeders/ShoppingSeeder.php"
safe_remove "database/seeders/TravelSeeder.php"
safe_remove "database/seeders/UserSeeder.php"
safe_remove "database/seeders/WeatherSeeder.php"
safe_remove "database/seeders/WordPairSeeder.php"
safe_remove "database/seeders/WorkJobSeekingSeeder.php"

echo ""
echo "=== Phase 8: Removing Frontend Pages ==="
safe_remove "resources/js/pages/Admin"
safe_remove "resources/js/pages/auth"
safe_remove "resources/js/pages/games"
safe_remove "resources/js/pages/settings"
safe_remove "resources/js/pages/vocabulary"
safe_remove "resources/js/pages/ErrorTesting.vue"
safe_remove "resources/js/pages/Welcome.vue"

echo ""
echo "=== Phase 9: Removing Game Components ==="
safe_remove "resources/js/components/games"
safe_remove "resources/js/components/DeleteUser.vue"
safe_remove "resources/js/components/UserInfo.vue"
safe_remove "resources/js/components/UserMenuContent.vue"

echo ""
echo "=== Phase 10: Removing Layouts ==="
safe_remove "resources/js/layouts/auth"
safe_remove "resources/js/layouts/settings"
safe_remove "resources/js/layouts/AuthLayout.vue"

echo ""
echo "=== Phase 11: Removing Composables ==="
safe_remove "resources/js/composables/useMatchMadnessGame.ts"
safe_remove "resources/js/composables/useInitials.ts"
safe_remove "resources/js/composables/useAppearance.ts"

echo ""
echo "=== Phase 12: Removing Route Files ==="
safe_remove "routes/admin.php"
safe_remove "routes/auth.php"
safe_remove "routes/settings.php"
safe_remove "routes/testing.php"
safe_remove "routes/vocabulary.php"

echo ""
echo "=== Phase 13: Removing Images and Assets ==="
safe_remove "public/swift_danish_logo.png"
safe_remove "resources/images/swift_danish_logo.png"
safe_remove "docs/designs/swift_danish_logo.png"
safe_remove "docs/designs/match_madness.png"
safe_remove "docs/designs/wireframe_games.png"

echo ""
echo "=== Phase 14: Removing Documentation ==="
safe_remove "docs/archived"
safe_remove "docs/archives"
safe_remove "docs/planning/2025_01_30_18_51_word_pairs_form_feature.md"
safe_remove "docs/planning/2025_05_30_14_36_architecture_improvements.md"
safe_remove "docs/planning/2025_05_30_14_36_bulk_select_delete_word_pairs.md"
safe_remove "docs/planning/2025_05_31_18_45_match_madness_configurable_pairs.md"

echo ""
echo "=== Cleanup Complete ==="
echo "All Swift Danish specific files have been moved to: $BACKUP_DIR"
echo ""
echo "Next steps:"
echo "1. Review the backup directory to ensure nothing important was removed"
echo "2. Update composer.json to remove any Swift Danish specific packages"
echo "3. Update package.json to remove any Swift Danish specific packages"
echo "4. Update DatabaseSeeder.php to remove Swift Danish seeders"
echo "5. Update web.php to remove Swift Danish routes"
echo "6. Update HandleInertiaRequests.php to remove auth-related shared data"
echo "7. Run 'composer dump-autoload' to update autoloader"
echo "8. Run 'npm run build' to rebuild assets"
echo ""
echo "To restore files, copy them back from $BACKUP_DIR"