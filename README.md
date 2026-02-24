Set-Content README.md @"
# GreatBuy Originals Web App | IMS to E-commerce Website

A high-performance Inventory Management System (IMS) integrated with an E-commerce storefront. Built with the "Modern Monolith" approach for maximum development efficiency.

## Tech Stack
- **Framework:** Laravel 12
- **Frontend:** Vue.js 3.5+ (Composition API)
- **Glue:** Inertia.js 2.0
- **Build Tool:** Vite 7
- **Database:** MySQL 8.0
- **Styling:** Tailwind CSS v4

## Getting Started

### Prerequisites
- PHP 8.3+
- Node.js 24+
- Composer 2.8+
- MySQL 8.0+

### Installation

1. **Clone the repository**
   \`\`\`bash
   git clone https://github.com/renzcuison/gb-web.git
   cd gb-web
   \`\`\`

2. **Install Backend Dependencies**
   \`\`\`bash
   composer install
   \`\`\`

3. **Install Frontend Dependencies**
   \`\`\`bash
   npm install --legacy-peer-deps
   \`\`\`

4. **Environment Setup**
   \`\`\`bash
   cp .env.example .env
   php artisan key:generate
   \`\`\`
   *Note: Open \`.env\` and configure your \`DB_DATABASE\`, \`DB_USERNAME\`, and \`DB_PASSWORD\`.*

5. **Run Migrations**
   \`\`\`bash
   php artisan migrate
   \`\`\`

6. **Start Development Servers**

   **Terminal 1 (Backend):**
   \`\`\`bash
   php artisan serve
   \`\`\`

   **Terminal 2 (Frontend/Vite):**
   \`\`\`bash
   npm run dev
   \`\`\`

## Features
- [x] Modern Monolith Architecture (Inertia.js)
- [ ] Product CRUD & Inventory Tracking
- [ ] Real-time Stock Syncing (Laravel Reverb)
- [ ] Responsive Admin Dashboard
- [ ] E-commerce Storefront Integration
"@