# Victorian Poems - AI-Powered Poetry Platform

A classic Victorian-themed platform for creating and sharing poetry with AI assistance.

## Features

- 🪶 **AI-Powered Generation** - Create poems using Groq AI (Llama models)
- ✒️ **Rich Text Editor** - Format poems with Victorian fonts and styling
- 🎨 **Victorian Aesthetic** - Elegant parchment backgrounds, ornate borders, sepia tones
- 📜 **Public Gallery** - Share your poems with the community
- 🏷️ **AI Categorization** - Automatic sentiment analysis and tagging
- 📤 **Export** - Download poems as PDFs or images
- 🔗 **Share Links** - Generate shareable links for your poems

## Tech Stack

- **Frontend**: SvelteKit 2 + Tailwind CSS
- **Backend**: Laravel 12 API
- **Database**: Firebase Firestore
- **Authentication**: Firebase Auth
- **AI**: Groq API (Llama 3.1 models)
- **Fonts**: Playfair Display, Cormorant Garamond, Crimson Text

## Project Structure

```
poems_viewer/
├── frontend/          # SvelteKit application
│   ├── src/
│   │   ├── lib/      # Components, services, stores
│   │   └── routes/   # SvelteKit pages
│   └── ...
├── backend/           # Laravel API
│   ├── app/
│   │   ├── Services/ # Groq AI service
│   │   └── Http/     # Controllers
│   └── routes/api.php
└── README.md
```

## Setup Instructions

### Prerequisites

- Node.js 18+ and npm
- PHP 8.2+ and Composer
- Firebase account
- Groq API key (free tier available at https://console.groq.com)

### Frontend Setup

1. Navigate to frontend directory:
   ```bash
   cd frontend
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Create `.env.local` from example:
   ```bash
   cp .env.local.example .env.local
   ```

4. Add your Firebase configuration to `.env.local`

5. Start development server:
   ```bash
   npm run dev
   ```

Frontend will run on `http://localhost:5173`

### Backend Setup

1. Navigate to backend directory:
   ```bash
   cd backend
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Create `.env` from example:
   ```bash
   cp .env.example .env
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Add your Groq API key to `.env`:
   ```env
   GROQ_API_KEY=your_groq_api_key_here
   ```

6. Run migrations (if using Laravel features):
   ```bash
   php artisan migrate
   ```

7. Start development server:
   ```bash
   php artisan serve
   ```

Backend will run on `http://localhost:8000`

### Firebase Setup

1. Go to https://console.firebase.google.com
2. Create a new project or select existing
3. Enable Firestore Database
4. Enable Authentication (Email/Password + Google)
5. Get your Firebase config from Project Settings
6. Add config to frontend `.env.local`
7. Set up Firestore security rules (see plan documentation)

### Getting Groq API Key

1. Go to https://console.groq.com
2. Sign up for free account
3. Navigate to API Keys section
4. Create new API key
5. Add to backend `.env` file

## Development

- Frontend dev server: `cd frontend && npm run dev`
- Backend dev server: `cd backend && php artisan serve`
- Both servers must be running for full functionality

## Environment Variables

### Frontend (.env.local)
- `VITE_FIREBASE_*` - Firebase configuration
- `VITE_API_BASE_URL` - Backend API URL

### Backend (.env)
- `GROQ_API_KEY` - Groq AI API key
- `FRONTEND_URL` - Frontend URL for CORS
- `APP_URL` - Backend URL

## API Endpoints

### AI Endpoints
- `POST /api/ai/generate-poem` - Generate poem from prompt
- `POST /api/ai/categorize` - Analyze poem sentiment and categories
- `POST /api/ai/generate-tags` - Generate tags for poem

### Health Check
- `GET /api/health` - API health status

## Victorian Design

The platform features a distinctive Victorian aesthetic:

- **Fonts**: Playfair Display, Cormorant Garamond, Crimson Text, Old Standard TT
- **Colors**: Parchment backgrounds, sepia tones, gold accents, ink brown text
- **Elements**: Ornate borders, decorative flourishes, parchment textures
- **No generic AI aesthetics** - Custom designed for classical elegance

## License

Private project - All rights reserved

## Support

For issues or questions, please refer to the implementation plan in `.claude/plans/`

---

Built with ❤️ and Claude Code
