/**
 * AI-Powered Image Search using TensorFlow.js
 * Enables visual product search in POS system
 */

class AIImageSearch {
    constructor() {
        this.model = null;
        this.modelLoaded = false;
        this.productEmbeddings = [];
        this.cacheName = 'nautilus-ai-cache-v1';
    }

    /**
     * Initialize the AI model
     */
    async initialize() {
        if (this.modelLoaded) return;

        try {
            console.log('Loading MobileNet model...');
            this.model = await mobilenet.load({
                version: 2,
                alpha: 1.0 // Full accuracy
            });
            this.modelLoaded = true;
            console.log('MobileNet model loaded successfully');

            // Load product embeddings from server
            await this.loadProductEmbeddings();
        } catch (error) {
            console.error('Error initializing AI model:', error);
            throw new Error('Failed to initialize AI search: ' + error.message);
        }
    }

    /**
     * Load product embeddings from server with caching
     */
    async loadProductEmbeddings() {
        try {
            // Try to load from cache first
            const cached = await this.getFromCache('product-embeddings');
            if (cached) {
                this.productEmbeddings = cached;
                console.log(`Loaded ${this.productEmbeddings.length} product embeddings from cache`);
                return;
            }

            // Fetch from server
            const response = await fetch('/store/api/product-embeddings');
            if (!response.ok) {
                throw new Error('Failed to fetch product embeddings');
            }

            this.productEmbeddings = await response.json();
            console.log(`Loaded ${this.productEmbeddings.length} product embeddings from server`);

            // Cache for future use
            await this.saveToCache('product-embeddings', this.productEmbeddings);
        } catch (error) {
            console.error('Error loading product embeddings:', error);
            this.productEmbeddings = [];
        }
    }

    /**
     * Search for products by image
     * @param {File|HTMLImageElement} image - Image file or element
     * @param {Object} options - Search options
     * @returns {Promise<Array>} - Ranked search results
     */
    async searchByImage(image, options = {}) {
        if (!this.modelLoaded) {
            await this.initialize();
        }

        const startTime = performance.now();

        try {
            // Convert to image element if needed
            const imgElement = await this.prepareImage(image);

            // Extract features from query image
            const queryEmbedding = await this.extractFeatures(imgElement);

            // Search through product embeddings
            const results = await this.findSimilarProducts(queryEmbedding, options);

            const searchTime = Math.round(performance.now() - startTime);
            console.log(`Search completed in ${searchTime}ms, found ${results.length} results`);

            // Log search history
            this.logSearchHistory(results, searchTime);

            return results;
        } catch (error) {
            console.error('Error during image search:', error);
            throw error;
        }
    }

    /**
     * Extract feature vector from image
     */
    async extractFeatures(imgElement) {
        // Get activation from second-to-last layer (feature vector)
        const activation = this.model.infer(imgElement, true);
        const embedding = await activation.data();
        activation.dispose(); // Clean up memory
        return Array.from(embedding);
    }

    /**
     * Find similar products using cosine similarity
     */
    async findSimilarProducts(queryEmbedding, options = {}) {
        const {
            categories = [],
            minSimilarity = 0.3,
            maxResults = 20,
            includeInactive = false
        } = options;

        // Filter products by category if specified
        let candidates = this.productEmbeddings;
        if (categories.length > 0) {
            candidates = candidates.filter(p => categories.includes(p.category));
        }

        // Calculate similarity scores
        const results = candidates.map(product => {
            const similarity = this.cosineSimilarity(queryEmbedding, product.embedding_vector);
            return {
                product_id: product.product_id,
                name: product.name,
                sku: product.sku,
                category: product.category,
                price: product.price,
                image_path: product.image_path,
                similarity: similarity,
                confidence: this.getConfidenceLevel(similarity)
            };
        });

        // Filter by minimum similarity and sort
        return results
            .filter(r => r.similarity >= minSimilarity)
            .sort((a, b) => b.similarity - a.similarity)
            .slice(0, maxResults);
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    cosineSimilarity(vecA, vecB) {
        if (vecA.length !== vecB.length) {
            throw new Error('Vectors must have same length');
        }

        let dotProduct = 0;
        let normA = 0;
        let normB = 0;

        for (let i = 0; i < vecA.length; i++) {
            dotProduct += vecA[i] * vecB[i];
            normA += vecA[i] * vecA[i];
            normB += vecB[i] * vecB[i];
        }

        if (normA === 0 || normB === 0) {
            return 0;
        }

        return dotProduct / (Math.sqrt(normA) * Math.sqrt(normB));
    }

    /**
     * Get confidence level description
     */
    getConfidenceLevel(similarity) {
        if (similarity >= 0.9) return 'Very High';
        if (similarity >= 0.8) return 'High';
        if (similarity >= 0.7) return 'Good';
        if (similarity >= 0.6) return 'Moderate';
        if (similarity >= 0.5) return 'Low';
        return 'Very Low';
    }

    /**
     * Prepare image for processing
     */
    async prepareImage(source) {
        if (source instanceof HTMLImageElement) {
            return source;
        }

        // If it's a File, create an image element
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = reject;

            if (source instanceof File) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    img.src = e.target.result;
                };
                reader.onerror = reject;
                reader.readAsDataURL(source);
            } else if (typeof source === 'string') {
                img.src = source;
            } else {
                reject(new Error('Invalid image source'));
            }
        });
    }

    /**
     * Cache management using IndexedDB
     */
    async saveToCache(key, data) {
        try {
            const cache = await caches.open(this.cacheName);
            const response = new Response(JSON.stringify(data));
            await cache.put(key, response);
        } catch (error) {
            console.warn('Failed to cache data:', error);
        }
    }

    async getFromCache(key) {
        try {
            const cache = await caches.open(this.cacheName);
            const response = await cache.match(key);
            if (response) {
                return await response.json();
            }
        } catch (error) {
            console.warn('Failed to retrieve from cache:', error);
        }
        return null;
    }

    /**
     * Log search history for analytics
     */
    async logSearchHistory(results, searchTimeMs) {
        try {
            const topResult = results[0];
            await fetch('/store/api/visual-search-log', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    top_result_product_id: topResult?.product_id,
                    similarity_score: topResult?.similarity,
                    results_count: results.length,
                    search_time_ms: searchTimeMs
                })
            });
        } catch (error) {
            console.warn('Failed to log search history:', error);
        }
    }

    /**
     * Refresh embeddings cache
     */
    async refreshCache() {
        const cache = await caches.open(this.cacheName);
        await cache.delete('product-embeddings');
        await this.loadProductEmbeddings();
    }

    /**
     * Generate embedding for a product image (admin tool)
     */
    async generateEmbedding(imageFile) {
        if (!this.modelLoaded) {
            await this.initialize();
        }

        const imgElement = await this.prepareImage(imageFile);
        const embedding = await this.extractFeatures(imgElement);

        return {
            embedding_vector: embedding,
            embedding_model: 'mobilenet_v2',
            embedding_quality_score: 1.0
        };
    }
}

// Export singleton instance
window.aiImageSearch = new AIImageSearch();
