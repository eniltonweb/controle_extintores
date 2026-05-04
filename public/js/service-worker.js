const CACHE_NAME = 'controle-extintores-cache-v1';
const urlsToCache = [
    '/formulario_inspecao.php',  // Cacheie o formulário de inspeção
    '/index.php',                // Página inicial
    '/css/styles.css',           // Seu arquivo CSS
    '/js/IndexedDB.js',          // Seu arquivo IndexedDB, se necessário
    '/js/service-worker.js',     // Este próprio arquivo Service Worker
    // Adicione outros arquivos essenciais que precisam ser cacheados
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                return cache.addAll(urlsToCache);
            })
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Retorna o recurso do cache, ou faz o fetch se não estiver no cache
                return response || fetch(event.request);
            })
    );
});

self.addEventListener('activate', event => {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (!cacheWhitelist.includes(cacheName)) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});