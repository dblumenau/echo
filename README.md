# Swift Danish (LDS) - Learn Danish Swiftly

An interactive language learning platform built with Laravel and Vue.js to help users learn Danish through engaging mini-games and personalized vocabulary practice.

## 🚀 Quick Setup

### Option 1: Using the setup script (Recommended)
```bash
git clone <repository-url>
cd lds
./bin/setup
```

### Option 2: Using Composer
```bash
git clone <repository-url>
cd lds
composer install
composer setup  # This runs all setup steps
```

### Option 3: Manual setup
```bash
git clone <repository-url>
cd lds

# Copy environment file
cp .env.example .env

# Install dependencies
composer install
npm install

# Generate application key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run migrations with seeders
php artisan migrate:fresh --seed

# Build assets
npm run build
```

## 🏃 Running the Application

### Development
```bash
# Start both Laravel and Vite dev servers
npm run dev  # In one terminal
php artisan serve  # In another terminal

# Or use the VS Code task (if using VS Code)
# Run Task > start
```

### Production Build
```bash
npm run build
```

## 🎮 Features

### Mini-Games
- **Match Madness** - Fast-paced vocabulary matching game with:
  - Category and difficulty filtering
  - Configurable timer (30-120 seconds)
  - Real-time progress tracking
  - Random word replacement delays (2-4 seconds)
  - Performance-based results with confetti celebrations
  - Play again functionality preserving settings

### Vocabulary Management
- **Word Pairs CRUD** - Complete management system for Danish-English vocabulary
- **Categories** - Organize words by topic (Colors, Food, Travel, etc.)
- **Difficulty Levels** - Easy, Medium, Hard classification
- **Bulk Operations** - Mass select and modify word pairs

### User Management
- **Multi-role System** - Superadmin, approved, and unapproved users
- **Registration Workflow** - Admin approval process for new users
- **Access Control** - Different feature access based on user status

## 🔐 Default Credentials

After running seeders, you can log in with (all passwords are `Password123`):

**Superadmin accounts:**
- dblumenau@gmail.com / Password123
- robvdwest.dk@gmail.com / Password123

**Test accounts:**
- approved@example.com / Password123 (Approved user with full vocabulary access)
- unapproved@example.com / Password123 (Limited access, awaiting approval)

## 📚 Documentation

- Project planning documents: `docs/planning/`
- Game designs: `docs/designs/`
- Claude AI instructions: `CLAUDE.md`

## 🛠️ Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Vue 3, Inertia.js 2, TypeScript
- **UI Components**: shadcn-vue with Tailwind 4
- **Database**: SQLite (with PostgreSQL compatibility)
- **Asset Bundler**: Vite
- **Authentication**: Laravel Breeze
- **Game Features**: Canvas-confetti for celebrations

## 📝 VS Code Workspace

This project includes a VS Code workspace file (`lds.code-workspace`) with pre-configured tasks:
- `start` - Run both Laravel and Vite servers
- `fresh-start` - Complete setup and start servers
- `test` - Run PHPUnit tests
- And more...

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage
```

## 🤝 Contributing

Please read the contributing guidelines in the Laravel documentation before submitting pull requests.

## 📄 License

This project is proprietary software. All rights reserved. This code is not open source and may not be used, modified, or distributed without explicit written permission from the owner.


## Still To Do
### Nag List
The button on the toast doesn't appear, clicking the toast does run the action but 
```js
toast('Event has been created', {
        description: 'Sunday, December 03, 2023 at 9:00 AM',
        action: {
          label: 'Undo',
          onClick: () => console.log('Undo'),
        },
      })
```
the label doesn't show as a label.
Check that intertia and laravel that push flash messages actually does display in the front end in toasts. Not sure if that really works. I just know that toast shows when vue does an actual check of the api call but if the backend putss something in flash I want that to show as well.
### To Do List
#### Word Pairs Table
- [ ] Add a bulk actions to the word pairs table to bulk delete word pairs, perhaps also bulk change category or difficulty level
