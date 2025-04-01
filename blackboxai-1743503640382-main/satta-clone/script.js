// Utility functions
const formatTime = date => {
    return date.toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit', 
        hour12: true 
    });
};

const showLoading = () => {
    document.querySelectorAll('.result-card').forEach(card => {
        const numberDisplay = card.querySelector('.number-display');
        if (numberDisplay) {
            numberDisplay.classList.add('loading');
        }
    });
};

const hideLoading = () => {
    document.querySelectorAll('.result-card').forEach(card => {
        const numberDisplay = card.querySelector('.number-display');
        if (numberDisplay) {
            numberDisplay.classList.remove('loading');
        }
    });
};

const showError = (message) => {
    clearError();
    const resultGrid = document.querySelector('.result-grid');
    if (!resultGrid) return;

    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <p>${message}</p>
        <button onclick="retryFetch()">Retry</button>
    `;
    
    resultGrid.appendChild(errorDiv);
};

const clearError = () => {
    const errorMessage = document.querySelector('.error-message');
    if (errorMessage) {
        errorMessage.remove();
    }
};

// Create particle effects
const createNumberParticles = (card) => {
    const particles = document.createElement('div');
    particles.className = 'number-particles';
    
    for (let i = 0; i < 10; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.setProperty('--delay', `${i * 0.1}s`);
        particle.style.setProperty('--angle', `${(i / 10) * 360}deg`);
        particles.appendChild(particle);
    }
    
    card.appendChild(particles);
    setTimeout(() => particles.remove(), 1000);
};

// Update a single result card
const updateResultCard = (card, result) => {
    const numberElement = card.querySelector('.number');
    const statusElement = card.querySelector('.status');
    const currentNumber = numberElement.textContent;
    
    // Only update if the number has changed
    if (numberElement && currentNumber !== result.result) {
        numberElement.classList.add('number-updating');
        createNumberParticles(card);
        
        // Animate number change
        gsap.timeline()
            .to(numberElement, {
                scale: 0.5,
                opacity: 0,
                duration: 0.2,
                onComplete: () => {
                    numberElement.textContent = result.result;
                }
            })
            .to(numberElement, {
                scale: 1.2,
                opacity: 1,
                duration: 0.3
            })
            .to(numberElement, {
                scale: 1,
                duration: 0.2,
                onComplete: () => {
                    numberElement.classList.remove('number-updating');
                }
            });
        
        // Update status
        if (statusElement) {
            statusElement.innerHTML = `<i class="fas fa-check-circle"></i> ${result.status}`;
            statusElement.className = `status ${result.status.toLowerCase()}`;
            gsap.from(statusElement, {
                scale: 0.8,
                opacity: 0,
                duration: 0.3
            });
        }
    }
};

// Fetch and update results
const fetchResults = async () => {
    clearError();
    showLoading();
    
    try {
        const response = await fetch('results.php');
        if (!response.ok) throw new Error('Failed to fetch results');
        
        const data = await response.json();
        
        // Update each game's result
        Object.entries(data.results).forEach(([key, result]) => {
            const card = document.querySelector(`[data-game="${key}"]`);
            if (card) updateResultCard(card, result);
        });
        
        // Update game list if needed
        if (data.games) {
            updateGamesList(data.games);
        }
        
        hideLoading();
        updateNextUpdateTime();
        
    } catch (error) {
        console.error('Error fetching results:', error);
        hideLoading();
        showError('Unable to fetch results. Please try again later.');
    }
};

// Update the games list if new games are added
const updateGamesList = (games) => {
    const resultGrid = document.querySelector('.result-grid');
    if (!resultGrid) return;
    
    // Check for new games
    games.forEach(game => {
        const existingCard = resultGrid.querySelector(`[data-game="${game.name}"]`);
        if (!existingCard) {
            const newCard = createGameCard(game);
            resultGrid.appendChild(newCard);
            gsap.from(newCard, {
                scale: 0.8,
                opacity: 0,
                duration: 0.5,
                ease: "back.out(1.7)"
            });
        }
    });
};

// Create a new game card
const createGameCard = (game) => {
    const card = document.createElement('div');
    card.className = 'result-card';
    card.setAttribute('data-game', game.name);
    
    card.innerHTML = `
        <div class="card-header">
            <h4>${game.display_name}</h4>
            <p class="time">${game.time_slot}</p>
        </div>
        <div class="number-display loading">
            <p class="number">--</p>
            <div class="number-animation"></div>
        </div>
        <span class="status pending">
            <i class="fas fa-clock"></i> PENDING
        </span>
    `;
    
    return card;
};

// Update countdown timer
const updateNextUpdateTime = () => {
    const countdownElement = document.getElementById('countdown');
    if (!countdownElement) return;
    
    let [minutes, seconds] = countdownElement.textContent.split(':').map(Number);
    
    clearInterval(window.countdownInterval);
    window.countdownInterval = setInterval(() => {
        if (seconds === 0) {
            if (minutes === 0) {
                clearInterval(window.countdownInterval);
                fetchResults();
                countdownElement.textContent = '05:00';
                updateNextUpdateTime();
                return;
            }
            minutes--;
            seconds = 59;
        } else {
            seconds--;
        }
        
        countdownElement.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }, 1000);
};

// Retry fetch function
const retryFetch = async () => {
    clearError();
    await fetchResults();
};

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Initial fetch
    fetchResults();
    
    // Set up periodic updates
    setInterval(fetchResults, 300000); // Every 5 minutes
    
    // Initialize countdown
    updateNextUpdateTime();
    
    // Add hover effects to result cards
    document.querySelectorAll('.result-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            gsap.to(card, {
                y: -5,
                scale: 1.02,
                duration: 0.3,
                ease: "power2.out"
            });
        });
        
        card.addEventListener('mouseleave', () => {
            gsap.to(card, {
                y: 0,
                scale: 1,
                duration: 0.3,
                ease: "power2.out"
            });
        });
    });
});
