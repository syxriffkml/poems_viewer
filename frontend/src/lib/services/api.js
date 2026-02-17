import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';

const apiClient = axios.create({
	baseURL: API_BASE_URL,
	headers: {
		'Content-Type': 'application/json',
		'Accept': 'application/json'
	},
	timeout: 45000
});

/**
 * @typedef {Object} PoemGenerationOptions
 * @property {string} [style] - Poem style (sonnet, haiku, etc.)
 * @property {string} [length] - Poem length (short, medium, long)
 * @property {string} [rhyme_scheme] - Rhyme scheme (AABB, ABAB, etc.)
 * @property {string} [tone] - Poem tone (joyful, melancholic, etc.)
 */

// AI Service endpoints
export const aiService = {
	/**
	 * Generate a poem using AI
	 * @param {string} prompt - The poem prompt
	 * @param {PoemGenerationOptions} [options={}] - Generation options
	 * @returns {Promise<any>}
	 */
	async generatePoem(prompt, options = {}) {
		const payload = {
			prompt,
			...(options.style && { style: options.style }),
			...(options.length && { length: options.length }),
			...(options.rhyme_scheme && { rhyme_scheme: options.rhyme_scheme }),
			...(options.tone && { tone: options.tone })
		};
		const response = await apiClient.post('/ai/generate-poem', payload);
		return response.data;
	},

	/**
	 * Categorize a poem using AI
	 * @param {string} content - The poem content
	 * @returns {Promise<any>}
	 */
	async categorizePoem(content) {
		const response = await apiClient.post('/ai/categorize', { content });
		return response.data;
	},

	/**
	 * Generate tags for a poem using AI
	 * @param {string} content - The poem content
	 * @returns {Promise<any>}
	 */
	async generateTags(content) {
		const response = await apiClient.post('/ai/generate-tags', { content });
		return response.data;
	}
};

export default apiClient;
