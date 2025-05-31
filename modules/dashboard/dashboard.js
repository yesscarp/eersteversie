// modules/dashboard/dashboard.js
class Dashboard {
    constructor() {
        this.currentSection = 'home-feed';
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadWeather();
        this.initializeRealTimeUpdates();
    }

    bindEvents() {
        // Navigation events
        document.querySelectorAll('[data-section]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.showSection(e.target.closest('[data-section]').dataset.section);
            });
        });

        // Post form submission
        const postForm = document.getElementById('createPostForm');
        if (postForm) {
            postForm.addEventListener('submit', (e) => this.handlePostSubmission(e));
        }

        // File upload preview
        const fileInput = document.getElementById('catch-photo');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleFilePreview(e));
        }

        // Like buttons
        document.querySelectorAll('.like-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleLike(e));
        });

        // Search functionality
        const searchInput = document.getElementById('global-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.handleSearch(e));
        }

        // Mobile menu toggle
        this.setupMobileMenu();
    }

    showSection(sectionId) {
        // Update navigation
        document.querySelectorAll('.sidebar-nav li').forEach(li => {
            li.classList.remove('active');
        });
        
        document.querySelector(`[data-section="${sectionId}"]`)?.closest('li')?.classList.add('active');

        // Update content
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });

        let targetSection = document.getElementById(sectionId);
        if (targetSection) {
            targetSection.classList.add('active');
        } else {
            // Load section dynamically if not exists
            this.loadSection(sectionId);
        }

        this.currentSection = sectionId;
    }

    async loadSection(sectionId) {
        const mainContent = document.querySelector('.dashboard-content');
        
        try {
            const response = await fetch(`sections/${sectionId}.php`);
            if (response.ok) {
                const html = await response.text();
                
                // Remove existing sections
                document.querySelectorAll('.content-section').forEach(section => {
                    section.classList.remove('active');
                });

                // Add new section
                const sectionDiv = document.createElement('div');
                sectionDiv.className = 'content-section active';
                sectionDiv.id = sectionId;
                sectionDiv.innerHTML = html;
                
                mainContent.appendChild(sectionDiv);
            }
        } catch (error) {
            console.error('Error loading section:', error);
            this.showNotification('Fout bij laden van sectie', 'error');
        }
    }

    async handlePostSubmission(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('description', document.getElementById('post-description').value);
        formData.append('fish_species', document.getElementById('fish-species').value);
        formData.append('weight', document.getElementById('fish-weight').value);
        formData.append('length', document.getElementById('fish-length').value);
        formData.append('location', document.getElementById('catch-location').value);
        
        const photoFile = document.getElementById('catch-photo').files[0];
        if (photoFile) {
            formData.append('photo', photoFile);
        }

        try {
            const response = await fetch('../../api/create-post.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Post succesvol gedeeld!', 'success');
                this.closePostModal();
                this.refreshFeed();
            } else {
                this.showNotification(result.message || 'Fout bij delen van post', 'error');
            }
        } catch (error) {
            console.error('Error creating post:', error);
            this.showNotification('Fout bij delen van post', 'error');
        }
    }

    handleFilePreview(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('photo-preview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.innerHTML = `
                    <div class="preview-image">
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="remove-preview" onclick="dashboard.removePreview()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        }
    }

    removePreview() {
        document.getElementById('photo-preview').innerHTML = '';
        document.getElementById('catch-photo').value = '';
    }

    async handleLike(e) {
        e.preventDefault();
        const btn = e.currentTarget;
        const catchId = btn.dataset.catchId;
        
        try {
            const response = await fetch('../../api/toggle-like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ catch_id: catchId })
            });

            const result = await response.json();
            
            if (result.success) {
                const icon = btn.querySelector('i');
                const countSpan = btn.querySelector('span') || btn.childNodes[1];
                
                if (result.liked) {
                    icon.className = 'fas fa-heart';
                    btn.style.color = '#e74c3c';
                } else {
                    icon.className = 'far fa-heart';
                    btn.style.color = '';
                }
                
                if (countSpan) {
                    countSpan.textContent = ` ${result.likes_count}`;
                }
            }
        } catch (error) {
            console.error('Error toggling like:', error);
        }
    }

    async handleSearch(e) {
        const query = e.target.value.trim();
        
        if (query.length < 2) {
            this.hideSearchResults();
            return;
        }

        try {
            const response = await fetch(`../../api/search.php?q=${encodeURIComponent(query)}`);
            const results = await response.json();
            
            this.showSearchResults(results);
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    showSearchResults(results) {
        // Implementation for search results dropdown
        console.log('Search results:', results);
    }

    hideSearchResults() {
        // Hide search results dropdown
    }

    async loadWeather() {
        try {
            const response = await fetch('../../api/weather.php');
            const weather = await response.json();
            
            if (weather.success) {
                this.updateWeatherWidget(weather.data);
            } else {
                this.showWeatherError();
            }
        } catch (error) {
            console.error('Weather error:', error);
            this.showWeatherError();
        }
    }

    updateWeatherWidget(weatherData) {
        const weatherContent = document.getElementById('weather-content');
        weatherContent.innerHTML = `
            <div class="weather-info">
                <div class="weather-main">
                    <img src="https:${weatherData.current.condition.icon}" alt="${weatherData.current.condition.text}">
                    <div class="temp">${weatherData.current.temp_c}°C</div>
                </div>
                <div class="weather-details">
                    <p class="condition">${weatherData.current.condition.text}</p>
                    <div class="weather-stats">
                        <span><i class="fas fa-eye"></i> ${weatherData.current.vis_km}km</span>
                        <span><i class="fas fa-wind"></i> ${weatherData.current.wind_kph}km/h</span>
                        <span><i class="fas fa-tint"></i> ${weatherData.current.humidity}%</span>
                    </div>
                </div>
            </div>
        `;
    }

    showWeatherError() {
        const weatherContent = document.getElementById('weather-content');
        weatherContent.innerHTML = `
            <div class="weather-error">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Weer info niet beschikbaar</span>
            </div>
        `;
    }

    async refreshFeed() {
        try {
            const response = await fetch('../../api/get-feed.php');
            const posts = await response.json();
            
            if (posts.success) {
                this.updateFeedPosts(posts.data);
            }
        } catch (error) {
            console.error('Error refreshing feed:', error);
        }
    }

    updateFeedPosts(posts) {
        const feedContainer = document.querySelector('.feed-posts');
        // Update feed with new posts
        // Implementation depends on specific requirements
    }

    initializeRealTimeUpdates() {
        // Set up periodic updates for live features
        setInterval(() => {
            this.updateOnlineStatus();
        }, 30000); // Update every 30 seconds
    }

    async updateOnlineStatus() {
        try {
            const response = await fetch('../../api/online-status.php');
            const status = await response.json();
            
            if (status.success) {
                // Update online friends count
                const onlineStatus = document.querySelector('.status-item.online span');
                if (onlineStatus) {
                    onlineStatus.textContent = `${status.online_friends} van ${status.total_friends} leden online`;
                }
            }
        } catch (error) {
            console.error('Error updating online status:', error);
        }
    }

    setupMobileMenu() {
        // Mobile menu toggle functionality
        const menuToggle = document.createElement('button');
        menuToggle.className = 'mobile-menu-toggle';
        menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
        menuToggle.style.display = 'none';
        
        document.querySelector('.top-bar').prepend(menuToggle);
        
        menuToggle.addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('open');
        });
        
        // Show/hide menu button based on screen size
        const checkScreenSize = () => {
            if (window.innerWidth <= 768) {
                menuToggle.style.display = 'block';
            } else {
                menuToggle.style.display = 'none';
                document.querySelector('.sidebar').classList.remove('open');
            }
        };
        
        window.addEventListener('resize', checkScreenSize);
        checkScreenSize();
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
}

// Modal functions
function openPostModal(type = 'general') {
    const modal = document.getElementById('postModal');
    modal.classList.add('show');
    
    // Focus on description field
    setTimeout(() => {
        document.getElementById('post-description').focus();
    }, 100);
}

function closePostModal() {
    const modal = document.getElementById('postModal');
    modal.classList.remove('show');
    
    // Reset form
    document.getElementById('createPostForm').reset();
    document.getElementById('photo-preview').innerHTML = '';
}

function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

// Close user menu when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('.user-menu')) {
        document.getElementById('userDropdown').classList.remove('show');
    }
});

// Initialize dashboard
const dashboard = new Dashboard();

// Global functions for inline event handlers
window.openPostModal = openPostModal;
window.closePostModal = closePostModal;
window.toggleUserMenu = toggleUserMenu;
window.dashboard = dashboard;
