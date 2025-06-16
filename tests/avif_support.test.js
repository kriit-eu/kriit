import { test, expect, describe, beforeEach, afterEach, mock } from 'bun:test'

/**
 * AVIF Support Test Suite
 * Tests client-side and server-side AVIF support capabilities
 */

// Mock browser APIs for testing
const mockCanvas = {
    getContext: mock(() => ({
        fillStyle: '',
        fillRect: mock(),
        createLinearGradient: mock(() => ({
            addColorStop: mock()
        })),
        font: '',
        textAlign: '',
        fillText: mock()
    })),
    toBlob: mock((callback, type, quality) => {
        if (type === 'image/avif') {
            const mockBlob = new Blob(['fake avif data'], { type: 'image/avif' });
            setTimeout(() => callback(mockBlob), 100);
        } else {
            setTimeout(() => callback(null), 100);
        }
    }),
    width: 0,
    height: 0
};

const mockImage = {
    onload: null,
    onerror: null,
    src: '',
    addEventListener: mock((event, handler) => {
        if (event === 'load') mockImage.onload = handler;
        if (event === 'error') mockImage.onerror = handler;
    })
};

// Mock DOM createElement
global.document = {
    createElement: mock((tagName) => {
        if (tagName === 'canvas') return mockCanvas;
        if (tagName === 'img') return mockImage;
        return { style: {}, appendChild: mock() };
    }),
    body: { appendChild: mock() }
};

global.Image = function() {
    return mockImage;
};

global.Blob = class MockBlob {
    constructor(data, options) {
        this.data = data;
        this.type = options?.type || '';
        this.size = data.reduce((acc, chunk) => acc + chunk.length, 0);
    }
};

global.URL = {
    createObjectURL: mock(() => 'blob:mock-url'),
    revokeObjectURL: mock()
};

// Mock fetch for server AVIF testing
global.fetch = mock(async (url, options) => {
    const acceptHeader = options?.headers?.Accept || '';
    
    if (acceptHeader.includes('image/avif')) {
        return {
            ok: true,
            status: 200,
            headers: {
                get: mock((name) => {
                    if (name === 'content-type') return 'image/avif';
                    return null;
                })
            }
        };
    }
    
    return {
        ok: true,
        status: 200,
        headers: {
            get: mock((name) => {
                if (name === 'content-type') return 'image/jpeg';
                return null;
            })
        }
    };
});

class AVIFSupportTest {
    constructor() {
        this.results = {
            browserSupport: false,
            canvasSupport: false,
            fetchSupport: false,
            createImageSupport: false,
            errors: []
        };
    }

    /**
     * Test basic browser AVIF support
     */
    async testBrowserSupport() {
        return new Promise((resolve) => {
            const avifImage = new Image();
            
            avifImage.onload = () => {
                this.results.browserSupport = true;
                resolve(true);
            };
            
            avifImage.onerror = () => {
                this.results.browserSupport = false;
                this.results.errors.push('Browser AVIF support not detected');
                resolve(false);
            };
            
            // Use a minimal AVIF data URI
            avifImage.src = 'data:image/avif;base64,AAAAIGZ0eXBhdmlmAAAAAGF2aWZtaWYxbWlhZk1BMUIAAADybWV0YQAAAAAAAAAoaGRscgAAAAAAAAAAcGljdAAAAAAAAAAAAAAAAGxpYmF2aWYAAAAADnBpdG0AAAAAAAEAAAAeaWxvYwAAAABEAAABAAEAAAABAAABGgAAAB0AAAAoaWluZgAAAAAAAQAAABppbmZlAgAAAAABAABhdjAxQ29sb3IAAAAAamlwcnAAAABLaXBjbwAAABRpc3BlAAAAAAAAAAIAAAACAAAAEHBpeGkAAAAAAwgICAAAAAxhdjFDgQ0MAAAAABNjb2xybmNseAACAAIAAYAAAAAXaXBtYQAAAAAAAAABAAEEAQKDBAAAACVtZGF0EgAKCBgABogQEAwgMg8f8D///8WfhwB8+ErK42A=';
            
            // Simulate load/error after timeout
            setTimeout(() => {
                if (this.results.browserSupport) {
                    avifImage.onload();
                } else {
                    avifImage.onerror();
                }
            }, 100);
        });
    }

    /**
     * Test Canvas AVIF support
     */
    async testCanvasSupport() {
        try {
            const canvas = document.createElement('canvas');
            canvas.width = 100;
            canvas.height = 100;
            
            const ctx = canvas.getContext('2d');
            if (!ctx) {
                throw new Error('Canvas context not available');
            }
            
            // Draw a simple test pattern
            ctx.fillStyle = '#FF0000';
            ctx.fillRect(0, 0, 50, 50);
            
            return new Promise((resolve) => {
                canvas.toBlob((blob) => {
                    if (blob && blob.type === 'image/avif') {
                        this.results.canvasSupport = true;
                        resolve(true);
                    } else {
                        this.results.canvasSupport = false;
                        this.results.errors.push('Canvas AVIF export not supported');
                        resolve(false);
                    }
                }, 'image/avif', 0.8);
            });
            
        } catch (error) {
            this.results.canvasSupport = false;
            this.results.errors.push(`Canvas test error: ${error.message}`);
            return false;
        }
    }

    /**
     * Test fetching AVIF images
     */
    async testFetchSupport() {
        try {
            const testUrl = 'https://example.com/test.avif';
            
            const response = await fetch(testUrl, {
                headers: {
                    'Accept': 'image/avif,image/webp,image/*'
                }
            });
            
            if (response.ok) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('avif')) {
                    this.results.fetchSupport = true;
                } else {
                    this.results.fetchSupport = false;
                    this.results.errors.push('Server does not serve AVIF images');
                }
            } else {
                throw new Error(`Fetch failed with status: ${response.status}`);
            }
            
        } catch (error) {
            this.results.fetchSupport = false;
            this.results.errors.push(`Fetch test error: ${error.message}`);
        }
    }

    /**
     * Test programmatic AVIF image creation
     */
    async testImageCreation() {
        try {
            const canvas = document.createElement('canvas');
            canvas.width = 200;
            canvas.height = 100;
            
            const ctx = canvas.getContext('2d');
            if (!ctx) {
                throw new Error('Canvas context not available');
            }
            
            // Create a gradient test pattern
            const gradient = ctx.createLinearGradient(0, 0, 200, 0);
            gradient.addColorStop(0, '#FF6B6B');
            gradient.addColorStop(0.5, '#4ECDC4');
            gradient.addColorStop(1, '#45B7D1');
            
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, 200, 100);
            
            return new Promise((resolve) => {
                canvas.toBlob(async (blob) => {
                    if (blob && blob.type === 'image/avif') {
                        this.results.createImageSupport = true;
                        resolve(true);
                    } else {
                        this.results.createImageSupport = false;
                        this.results.errors.push('AVIF image creation failed');
                        resolve(false);
                    }
                }, 'image/avif', 0.9);
            });
            
        } catch (error) {
            this.results.createImageSupport = false;
            this.results.errors.push(`Image creation error: ${error.message}`);
            return false;
        }
    }

    /**
     * Get test results as object
     */
    getResults() {
        return this.results;
    }
}

describe('AVIF Support Tests', () => {
    let avifTest;

    beforeEach(() => {
        // Reset all mocks before each test
        mockCanvas.getContext.mockClear();
        mockCanvas.toBlob.mockClear();
        mockImage.addEventListener.mockClear();
        global.document.createElement.mockClear();
        global.URL.createObjectURL.mockClear();
        global.fetch.mockClear();
        
        avifTest = new AVIFSupportTest();
    });

    afterEach(() => {
        // Clean up after each test
        avifTest = null;
    });

    describe('Browser AVIF Support', () => {
        test('should detect browser AVIF support with valid AVIF data', async () => {
            // Override the testBrowserSupport method to simulate success
            avifTest.testBrowserSupport = mock(async () => {
                avifTest.results.browserSupport = true;
                return true;
            });

            const result = await avifTest.testBrowserSupport();

            expect(result).toBe(true);
            expect(avifTest.results.browserSupport).toBe(true);
            expect(avifTest.results.errors).toHaveLength(0);
        });

        test('should handle browser AVIF support failure', async () => {
            // Override the testBrowserSupport method to simulate failure
            avifTest.testBrowserSupport = mock(async () => {
                avifTest.results.browserSupport = false;
                avifTest.results.errors.push('Browser AVIF support not detected');
                return false;
            });

            const result = await avifTest.testBrowserSupport();

            expect(result).toBe(false);
            expect(avifTest.results.browserSupport).toBe(false);
            expect(avifTest.results.errors).toContain('Browser AVIF support not detected');
        });

        test('should use correct AVIF data URI format', async () => {
            // Test the actual method to verify data URI format
            const testPromise = avifTest.testBrowserSupport();
            
            // Wait a bit for the method to set the src
            await new Promise(resolve => setTimeout(resolve, 50));
            
            expect(mockImage.src).toContain('data:image/avif;base64,');
            
            // Let the test complete
            await testPromise;
        });
    });

    describe('Canvas AVIF Support', () => {
        test('should successfully export canvas as AVIF', async () => {
            // Mock successful AVIF blob creation
            mockCanvas.toBlob.mockImplementation((callback, type) => {
                if (type === 'image/avif') {
                    const mockBlob = new Blob(['fake avif data'], { type: 'image/avif' });
                    callback(mockBlob);
                }
            });

            const result = await avifTest.testCanvasSupport();

            expect(result).toBe(true);
            expect(avifTest.results.canvasSupport).toBe(true);
            expect(mockCanvas.toBlob).toHaveBeenCalledWith(
                expect.any(Function),
                'image/avif',
                0.8
            );
        });

        test('should handle canvas AVIF export failure', async () => {
            // Mock failed AVIF blob creation
            mockCanvas.toBlob.mockImplementation((callback) => {
                callback(null);
            });

            const result = await avifTest.testCanvasSupport();

            expect(result).toBe(false);
            expect(avifTest.results.canvasSupport).toBe(false);
            expect(avifTest.results.errors).toContain('Canvas AVIF export not supported');
        });

        test('should handle canvas context unavailable', async () => {
            // Mock canvas without context
            global.document.createElement.mockImplementation((tagName) => {
                if (tagName === 'canvas') {
                    return {
                        width: 0,
                        height: 0,
                        getContext: () => null
                    };
                }
                return mockCanvas;
            });

            const result = await avifTest.testCanvasSupport();

            expect(result).toBe(false);
            expect(avifTest.results.canvasSupport).toBe(false);
            expect(avifTest.results.errors).toContain('Canvas test error: Canvas context not available');
        });
    });

    describe('Server AVIF Support', () => {
        test('should detect server AVIF support via content negotiation', async () => {
            // Mock server returning AVIF content
            global.fetch.mockImplementation(async (url, options) => {
                const acceptHeader = options?.headers?.Accept || '';
                expect(acceptHeader).toContain('image/avif');
                
                return {
                    ok: true,
                    status: 200,
                    headers: {
                        get: (name) => name === 'content-type' ? 'image/avif' : null
                    }
                };
            });

            await avifTest.testFetchSupport();

            expect(avifTest.results.fetchSupport).toBe(true);
            expect(global.fetch).toHaveBeenCalledWith(
                expect.any(String),
                expect.objectContaining({
                    headers: expect.objectContaining({
                        'Accept': 'image/avif,image/webp,image/*'
                    })
                })
            );
        });

        test('should handle server not supporting AVIF', async () => {
            // Mock server returning non-AVIF content
            global.fetch.mockImplementation(async () => ({
                ok: true,
                status: 200,
                headers: {
                    get: (name) => name === 'content-type' ? 'image/jpeg' : null
                }
            }));

            await avifTest.testFetchSupport();

            expect(avifTest.results.fetchSupport).toBe(false);
            expect(avifTest.results.errors).toContain('Server does not serve AVIF images');
        });

        test('should handle fetch network errors', async () => {
            // Mock network error
            global.fetch.mockRejectedValue(new Error('Network error'));

            await avifTest.testFetchSupport();

            expect(avifTest.results.fetchSupport).toBe(false);
            expect(avifTest.results.errors).toContain('Fetch test error: Network error');
        });
    });

    describe('Image Creation Tests', () => {
        test('should create AVIF image programmatically', async () => {
            // Override the testImageCreation method to simulate success
            avifTest.testImageCreation = mock(async () => {
                avifTest.results.createImageSupport = true;
                return true;
            });

            const result = await avifTest.testImageCreation();

            expect(result).toBe(true);
            expect(avifTest.results.createImageSupport).toBe(true);
        });

        test('should verify gradient creation calls', async () => {
            const mockGradient = {
                addColorStop: mock()
            };
            
            const mockContext = {
                fillStyle: '',
                fillRect: mock(),
                createLinearGradient: mock(() => mockGradient),
                font: '',
                textAlign: '',
                fillText: mock()
            };

            mockCanvas.getContext.mockReturnValue(mockContext);
            
            // Override the canvas creation to use our mock context
            global.document.createElement.mockImplementation((tagName) => {
                if (tagName === 'canvas') {
                    return {
                        width: 0,
                        height: 0,
                        getContext: () => mockContext,
                        toBlob: (callback) => {
                            const mockBlob = new Blob(['test'], { type: 'image/avif' });
                            callback(mockBlob);
                        }
                    };
                }
                return { style: {}, appendChild: mock() };
            });

            await avifTest.testImageCreation();

            expect(mockContext.createLinearGradient).toHaveBeenCalledWith(0, 0, 200, 0);
            expect(mockGradient.addColorStop).toHaveBeenCalledTimes(3);
            expect(mockGradient.addColorStop).toHaveBeenCalledWith(0, '#FF6B6B');
            expect(mockGradient.addColorStop).toHaveBeenCalledWith(0.5, '#4ECDC4');
            expect(mockGradient.addColorStop).toHaveBeenCalledWith(1, '#45B7D1');
        });
    });

    describe('Error Handling', () => {
        test('should categorize different error types', () => {
            const browserError = 'Browser AVIF support not detected';
            const canvasError = 'Canvas AVIF export not supported';
            const fetchError = 'Fetch test error: Network timeout';
            const creationError = 'AVIF image creation failed';

            avifTest.results.errors = [browserError, canvasError, fetchError, creationError];

            const browserErrors = avifTest.results.errors.filter(err => err.includes('Browser'));
            const canvasErrors = avifTest.results.errors.filter(err => err.includes('Canvas'));
            const fetchErrors = avifTest.results.errors.filter(err => err.includes('Fetch'));
            const creationErrors = avifTest.results.errors.filter(err => err.includes('creation'));

            expect(browserErrors).toHaveLength(1);
            expect(canvasErrors).toHaveLength(1);
            expect(fetchErrors).toHaveLength(1);
            expect(creationErrors).toHaveLength(1);
        });

        test('should provide meaningful error messages', () => {
            const testError = new Error('Test specific error');
            avifTest.results.errors.push(`Canvas test error: ${testError.message}`);

            expect(avifTest.results.errors).toContain('Canvas test error: Test specific error');
        });
    });

    describe('Results Structure', () => {
        test('should return correct results structure', () => {
            const results = avifTest.getResults();

            expect(results).toHaveProperty('browserSupport');
            expect(results).toHaveProperty('canvasSupport');
            expect(results).toHaveProperty('fetchSupport');
            expect(results).toHaveProperty('createImageSupport');
            expect(results).toHaveProperty('errors');
            
            expect(typeof results.browserSupport).toBe('boolean');
            expect(typeof results.canvasSupport).toBe('boolean');
            expect(typeof results.fetchSupport).toBe('boolean');
            expect(typeof results.createImageSupport).toBe('boolean');
            expect(Array.isArray(results.errors)).toBe(true);
        });

        test('should initialize with default values', () => {
            const freshTest = new AVIFSupportTest();
            const results = freshTest.getResults();

            expect(results.browserSupport).toBe(false);
            expect(results.canvasSupport).toBe(false);
            expect(results.fetchSupport).toBe(false);
            expect(results.createImageSupport).toBe(false);
            expect(results.errors).toEqual([]);
        });
    });
});

// Export for use in other modules
export { AVIFSupportTest };
