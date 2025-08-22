# Modern JavaScript & Build Tools

WP-Skin provides seamless integration with modern JavaScript frameworks and build tools, powered by Vite for optimal development experience.

## Build System Overview

### Vite Integration

WP-Skin uses **Vite** as the primary build tool for its speed and modern features:

- **Hot Module Replacement (HMR)** - Instant updates during development
- **ES Modules** - Native ESM support for faster builds
- **Framework Support** - Built-in React, Vue, Svelte support
- **Automatic optimization** - Tree shaking, code splitting, minification

### Development Workflow

```bash
# Start development server with HMR
cd resources
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview
```

## Framework Support

### React Integration

#### Setup

```bash
cd resources
npm install @vitejs/plugin-react react react-dom
```

Update Vite configuration:

```javascript
// resources/vite.config.mjs
import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
import react from "@vitejs/plugin-react";

export default defineConfig({
  plugins: [react(), tailwindcss()],
  build: {
    outDir: "dist",
    emptyOutDir: true,
    rollupOptions: {
      input: {
        app: "./src/js/app.js",
      },
      output: {
        entryFileNames: "js/[name].[hash].js",
        chunkFileNames: "js/[name].[hash].js",
        assetFileNames: (assetInfo) => {
          if (assetInfo.name.endsWith(".css")) {
            return "css/[name].[hash].css";
          }
          return "assets/[name].[hash][extname]";
        },
      },
    },
    manifest: true,
  },
  server: {
    host: "0.0.0.0",
    port: 5173,
    cors: true,
    strictPort: true,
  },
});
```
```

#### React Components

Create React components in your JavaScript:

```javascript
// resources/src/js/components/PostCard.jsx
import React from 'react';

const PostCard = ({ title, excerpt, image, link }) => {
  return (
    <div className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
      {image && (
        <img 
          src={image} 
          alt={title}
          className="w-full h-48 object-cover"
        />
      )}
      <div className="p-6">
        <h2 className="text-xl font-semibold text-gray-900 mb-2">
          {title}
        </h2>
        <p className="text-gray-600 mb-4">
          {excerpt}
        </p>
        <a 
          href={link}
          className="inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition-colors"
        >
          Read More
        </a>
      </div>
    </div>
  );
};

export default PostCard;
```

#### WordPress Integration

```javascript
// resources/src/js/app.js
import '../css/app.css';
import React from 'react';
import { createRoot } from 'react-dom/client';
import PostCard from './components/PostCard';

// Mount React components on specific elements
document.addEventListener('DOMContentLoaded', () => {
  // Mount post cards
  const postCardElements = document.querySelectorAll('[data-react-post-card]');
  postCardElements.forEach(element => {
    const props = JSON.parse(element.dataset.reactPostCard || '{}');
    const root = createRoot(element);
    root.render(<PostCard {...props} />);
  });
});
```

Use in PHP templates:

```php
<?php
// In your WordPress templates
$post_data = [
    'title' => get_the_title(),
    'excerpt' => get_the_excerpt(),
    'image' => get_the_post_thumbnail_url(),
    'link' => get_permalink()
];
?>

<div data-react-post-card='<?php echo esc_attr(json_encode($post_data)); ?>'></div>
```

### Vue.js Integration

#### Setup

```bash
cd resources
npm install @vitejs/plugin-vue vue
```

Update Vite configuration:

```javascript
// resources/vite.config.mjs
import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
  plugins: [vue(), tailwindcss()],
  // ... rest of config same as above
});
```
```

#### Vue Components

```vue
<!-- resources/src/js/components/ContactForm.vue -->
<template>
  <form @submit.prevent="submitForm" class="space-y-6">
    <div>
      <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
        Name
      </label>
      <input 
        id="name"
        v-model="form.name"
        type="text" 
        required
        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
    </div>
    
    <div>
      <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
        Email
      </label>
      <input 
        id="email"
        v-model="form.email"
        type="email" 
        required
        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
    </div>
    
    <div>
      <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
        Message
      </label>
      <textarea 
        id="message"
        v-model="form.message"
        rows="5" 
        required
        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
      ></textarea>
    </div>
    
    <button 
      type="submit"
      :disabled="submitting"
      class="w-full bg-blue-500 hover:bg-blue-600 disabled:bg-blue-300 text-white py-3 rounded transition-colors"
    >
      {{ submitting ? 'Sending...' : 'Send Message' }}
    </button>
  </form>
</template>

<script>
import { ref } from 'vue';

export default {
  name: 'ContactForm',
  setup() {
    const form = ref({
      name: '',
      email: '',
      message: ''
    });
    const submitting = ref(false);

    const submitForm = async () => {
      submitting.value = true;
      
      try {
        const response = await fetch('/wp-admin/admin-ajax.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
            action: 'contact_form_submit',
            nonce: wpData.nonce,
            ...form.value
          })
        });
        
        const result = await response.json();
        
        if (result.success) {
          alert('Message sent successfully!');
          form.value = { name: '', email: '', message: '' };
        } else {
          alert('Error sending message: ' + result.data);
        }
      } catch (error) {
        alert('Network error: ' + error.message);
      } finally {
        submitting.value = false;
      }
    };

    return {
      form,
      submitting,
      submitForm
    };
  }
};
</script>
```

### Svelte Integration

#### Setup

```bash
cd resources
npm install @sveltejs/vite-plugin-svelte svelte
```

Configuration:

```javascript
// resources/vite.config.mjs
import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
import { svelte } from "@sveltejs/vite-plugin-svelte";

export default defineConfig({
  plugins: [svelte(), tailwindcss()],
  // ... rest of config same as above
});
```
```

#### Svelte Components

```svelte
<!-- resources/src/js/components/SearchWidget.svelte -->
<script>
  import { onMount } from 'svelte';
  
  let searchTerm = '';
  let results = [];
  let loading = false;
  
  const searchPosts = async () => {
    if (!searchTerm.trim()) {
      results = [];
      return;
    }
    
    loading = true;
    
    try {
      const response = await fetch(`/wp-json/wp/v2/posts?search=${encodeURIComponent(searchTerm)}&_embed`);
      results = await response.json();
    } catch (error) {
      console.error('Search failed:', error);
      results = [];
    } finally {
      loading = false;
    }
  };
  
  // Debounced search
  let searchTimeout;
  $: {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(searchPosts, 300);
  }
</script>

<div class="relative">
  <input 
    bind:value={searchTerm}
    placeholder="Search posts..."
    class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
  >
  
  <div class="absolute right-3 top-2.5">
    {#if loading}
      <div class="animate-spin h-5 w-5 border-2 border-blue-500 border-t-transparent rounded-full"></div>
    {:else}
      <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
      </svg>
    {/if}
  </div>
  
  {#if results.length > 0}
    <div class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-96 overflow-y-auto z-50">
      {#each results as post}
        <a 
          href={post.link} 
          class="block p-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0"
        >
          <h3 class="font-medium text-gray-900">{@html post.title.rendered}</h3>
          <p class="text-sm text-gray-600 mt-1">{@html post.excerpt.rendered}</p>
        </a>
      {/each}
    </div>
  {/if}
</div>
```

### Alpine.js Integration

For lighter interactivity, WP-Skin also supports Alpine.js:

```bash
cd resources
npm install alpinejs
```

```javascript
// resources/src/js/app.js
import '../css/app.css';
import Alpine from 'alpinejs';

// Alpine.js components
Alpine.data('postCard', () => ({
  liked: false,
  likes: 0,
  
  init() {
    this.likes = parseInt(this.$el.dataset.likes || '0');
  },
  
  toggleLike() {
    this.liked = !this.liked;
    this.likes += this.liked ? 1 : -1;
    
    // Send to server
    fetch('/wp-admin/admin-ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'toggle_like',
        post_id: this.$el.dataset.postId,
        nonce: wpData.nonce
      })
    });
  }
}));

Alpine.start();
```

Use in PHP:

```php
<?php
// In your templates
?>
<div 
  x-data="postCard" 
  data-post-id="<?php echo get_the_ID(); ?>"
  data-likes="<?php echo get_post_meta(get_the_ID(), 'likes', true) ?: 0; ?>"
  class="bg-white rounded-lg shadow-md p-6"
>
  <h2 class="text-xl font-semibold mb-4"><?php the_title(); ?></h2>
  <p class="text-gray-600 mb-4"><?php the_excerpt(); ?></p>
  
  <button 
    @click="toggleLike" 
    :class="liked ? 'bg-red-500 text-white' : 'bg-gray-200 text-gray-700'"
    class="px-4 py-2 rounded transition-colors"
  >
    <span x-show="!liked">♡</span>
    <span x-show="liked">❤️</span>
    <span x-text="likes"></span>
  </button>
</div>
```

## WordPress REST API Integration

### Fetching Data

```javascript
// resources/src/js/utils/wp-api.js
class WPApi {
  constructor() {
    this.baseUrl = wpApiSettings?.root || '/wp-json/wp/v2/';
    this.nonce = wpApiSettings?.nonce || '';
  }
  
  async get(endpoint, params = {}) {
    const url = new URL(endpoint, this.baseUrl);
    Object.keys(params).forEach(key => {
      url.searchParams.append(key, params[key]);
    });
    
    const response = await fetch(url, {
      headers: {
        'X-WP-Nonce': this.nonce
      }
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    return response.json();
  }
  
  async post(endpoint, data = {}) {
    const response = await fetch(new URL(endpoint, this.baseUrl), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': this.nonce
      },
      body: JSON.stringify(data)
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    return response.json();
  }
}

export default new WPApi();
```

### Using the API

```javascript
// resources/src/js/components/PostList.jsx
import React, { useState, useEffect } from 'react';
import WPApi from '../utils/wp-api';

const PostList = ({ category = '', limit = 10 }) => {
  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchPosts = async () => {
      try {
        setLoading(true);
        const params = {
          per_page: limit,
          _embed: true
        };
        
        if (category) {
          params.categories = category;
        }
        
        const postsData = await WPApi.get('posts', params);
        setPosts(postsData);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchPosts();
  }, [category, limit]);

  if (loading) {
    return <div className="text-center py-8">Loading posts...</div>;
  }

  if (error) {
    return <div className="text-red-600 text-center py-8">Error: {error}</div>;
  }

  return (
    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
      {posts.map(post => (
        <article key={post.id} className="bg-white rounded-lg shadow-md overflow-hidden">
          {post._embedded?.['wp:featuredmedia']?.[0] && (
            <img 
              src={post._embedded['wp:featuredmedia'][0].source_url}
              alt={post.title.rendered}
              className="w-full h-48 object-cover"
            />
          )}
          <div className="p-6">
            <h2 className="text-xl font-semibold mb-2">
              <a 
                href={post.link} 
                className="text-gray-900 hover:text-blue-600"
                dangerouslySetInnerHTML={{ __html: post.title.rendered }}
              />
            </h2>
            <div 
              className="text-gray-600"
              dangerouslySetInnerHTML={{ __html: post.excerpt.rendered }}
            />
          </div>
        </article>
      ))}
    </div>
  );
};

export default PostList;
```

## Asset Management

### Automatic Asset Detection

WP-Skin automatically detects and loads built assets:

```php
// In your skin.php file
use WordPressSkin\Core\Skin;

// Assets are auto-detected from resources/dist/
Skin::assets()
    ->css('app')    // Loads resources/dist/app.css
    ->js('app');    // Loads resources/dist/app.js
```

### Manual Asset Loading

For custom assets or external libraries:

```php
Skin::hooks()
    ->action('wp_enqueue_scripts', function() {
        // External libraries
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], '3.9.1', true);
        
        // Custom scripts
        wp_enqueue_script(
            'theme-interactive',
            get_template_directory_uri() . '/resources/dist/interactive.js',
            ['jquery'],
            filemtime(get_template_directory() . '/resources/dist/interactive.js'),
            true
        );
        
        // Localize data for JavaScript
        wp_localize_script('theme-interactive', 'wpData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_rest'),
            'apiUrl' => rest_url('wp/v2/')
        ]);
    });
```

### Development vs Production

WP-Skin automatically handles asset loading based on environment:

**Development Mode** (with Vite dev server):
- Assets loaded from `http://localhost:5173`
- Hot module replacement enabled
- Source maps available

**Production Mode**:
- Assets loaded from `resources/dist/`
- Minified and optimized
- Cache busting via manifest

## TypeScript Support

Enable TypeScript for better development experience:

```bash
cd resources
npm install -D typescript @types/node
```

Create TypeScript config:

```json
// resources/tsconfig.json
{
  "compilerOptions": {
    "target": "ES2020",
    "lib": ["ES2020", "DOM", "DOM.Iterable"],
    "module": "ESNext",
    "skipLibCheck": true,
    "moduleResolution": "bundler",
    "allowImportingTsExtensions": true,
    "resolveJsonModule": true,
    "isolatedModules": true,
    "noEmit": true,
    "jsx": "react-jsx",
    "strict": true,
    "noUnusedLocals": true,
    "noUnusedParameters": true,
    "noFallthroughCasesInSwitch": true,
    "baseUrl": ".",
    "paths": {
      "@/*": ["./src/*"]
    }
  },
  "include": ["src"],
  "references": [{ "path": "./tsconfig.node.json" }]
}
```

## Performance Optimization

### Code Splitting

```javascript
// resources/vite.config.mjs
export default defineConfig({
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['react', 'react-dom'],
          utils: ['lodash', 'moment']
        }
      }
    }
  }
});
```

### Lazy Loading

```javascript
// resources/src/js/app.js
import { lazy, Suspense } from 'react';

const LazyComponent = lazy(() => import('./components/HeavyComponent'));

function App() {
  return (
    <Suspense fallback={<div>Loading...</div>}>
      <LazyComponent />
    </Suspense>
  );
}
```

### Service Worker (Optional)

```javascript
// resources/src/js/sw.js
const CACHE_NAME = 'wp-skin-v1';
const CACHE_URLS = [
  '/',
  '/wp-content/themes/your-theme/resources/dist/app.css',
  '/wp-content/themes/your-theme/resources/dist/app.js'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(CACHE_URLS))
  );
});
```

## Testing

### Jest Setup

```bash
cd resources
npm install -D jest @testing-library/react @testing-library/jest-dom
```

```javascript
// resources/jest.config.js
module.exports = {
  testEnvironment: 'jsdom',
  setupFilesAfterEnv: ['<rootDir>/src/setupTests.js'],
  moduleNameMapping: {
    '^@/(.*)$': '<rootDir>/src/$1'
  }
};
```

### Component Testing

```javascript
// resources/src/__tests__/PostCard.test.jsx
import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import PostCard from '../components/PostCard';

test('renders post card with title', () => {
  const props = {
    title: 'Test Post',
    excerpt: 'This is a test excerpt',
    link: '/test-post'
  };
  
  render(<PostCard {...props} />);
  
  expect(screen.getByText('Test Post')).toBeInTheDocument();
  expect(screen.getByText('This is a test excerpt')).toBeInTheDocument();
});
```

## Best Practices

1. **Use ES Modules** - Leverage modern import/export syntax
2. **Component-based architecture** - Create reusable, focused components
3. **Type safety** - Use TypeScript for larger projects
4. **Performance** - Implement code splitting and lazy loading
5. **Testing** - Write tests for complex components
6. **Progressive enhancement** - Ensure basic functionality without JavaScript