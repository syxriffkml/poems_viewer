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

// AI Service endpoints
export const aiService = {
	async generatePoem(prompt) {
		const response = await apiClient.post('/ai/generate-poem', { prompt });
		return response.data;
	},

	async categorizePoem(content) {
		const response = await apiClient.post('/ai/categorize', { content });
		return response.data;
	},

	async generateTags(content) {
		const response = await apiClient.post('/ai/generate-tags', { content });
		return response.data;
	}
};

export default apiClient;
