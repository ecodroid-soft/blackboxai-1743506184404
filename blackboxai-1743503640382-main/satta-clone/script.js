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
        <div class="flex flex-col items-center gap-4 p-6">
            <i class="fas fa-face-frown text-4xl text-yellow-500 animate-bounce"></i>
            <div class="text-center">
                <p class="text-lg mb-2">Oops! We're having trouble fetching the latest results.</p>
                <p class="text-gray-400 mb-4">Don't worry, we're working on it! Please try again in a few moments.</p>
            </div>
            <button onclick="retryFetch()" class="bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700 transition-colors flex items-center gap-2">
                <i class="fas fa-sync-alt"></i>
                Try Again
            </button>
        </div>
    `;
    
    resultGrid.appendChild(errorDiv);
};

const clearError = () => {
    const errorMessage = document.querySelector('.error-message');
    if (errorMessage) {
        errorMessage.remove();
    }
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
        
        hideLoading();
        updateNextUpdateTime();
        
    } catch (error) {
        console.error('Error fetching results:', error);
        hideLoading();
        showError('Unable to fetch results. Please try again later.');
    }
};

// Update a single result card
const updateResultCard = (card, result) => {
    const numberElement = card.querySelector('.number');
    const statusElement = card.querySelector('.status');
    const currentNumber = numberElement.textContent;
    
    // Only update if the number has changed
    if (numberElement && currentNumber !== result.result) {
        numberElement.classList.add('number-updating');
        
        // Animate number change
        numberElement.style.transform = 'scale(0.5)';
        numberElement.style.opacity = '0';
        
        setTimeout(() => {
            numberElement.textContent = result.result;
            numberElement.style.transform = 'scale(1.2)';
            numberElement.style.opacity = '1';
            
            setTimeout(() => {
                numberElement.style.transform = 'scale(1)';
                numberElement.classList.remove('number-updating');
            }, 200);
        }, 200);
        
        // Update status
        if (statusElement) {
            statusElement.innerHTML = `<i class="fas fa-check-circle"></i> ${result.status}`;
            statusElement.className = `status ${result.status.toLowerCase()}`;
        }
    }
};

// Handle historical results table
document.addEventListener('DOMContentLoaded', () => {
    // Make date cells sticky on horizontal scroll
    const sheetsTable = document.querySelector('.sheets-table');
    if (sheetsTable) {
        sheetsTable.addEventListener('scroll', () => {
            const dateCells = document.querySelectorAll('.date-cell');
            dateCells.forEach(cell => {
                cell.style.transform = `translateX(${sheetsTable.scrollLeft}px)`;
            });
        });
    }

    // Initialize
    fetchResults();
    updateNextUpdateTime();
});

// Retry fetch function
const retryFetch = async () => {
    clearError();
    await fetchResults();
};
