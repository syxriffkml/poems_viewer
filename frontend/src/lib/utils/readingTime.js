/**
 * Calculate estimated reading time for a poem
 * @param {string} content - The poem content
 * @returns {string} - Formatted reading time (e.g., "< 1 min read", "~2 min read")
 */
export function calculateReadingTime(content) {
	if (!content) return '< 1 min read';

	// Count words (split by whitespace and filter empty strings)
	const words = content.trim().split(/\s+/).filter(word => word.length > 0);
	const wordCount = words.length;

	// Average reading speed: 200 words per minute
	const wordsPerMinute = 200;
	const minutes = Math.ceil(wordCount / wordsPerMinute);

	// Format output
	if (minutes < 1) {
		return '< 1 min read';
	} else if (minutes === 1) {
		return '~1 min read';
	} else {
		return `~${minutes} min read`;
	}
}
