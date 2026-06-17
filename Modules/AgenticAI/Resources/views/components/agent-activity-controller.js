/**
 * Agent Activity Panel Controller
 * Production-grade UX state management
 */

const AgentActivityController = {
    panel: null,
    currentState: 'IDLE',
    steps: [],
    
    /**
     * Initialize the controller
     */
    init() {
        this.panel = document.getElementById('agentActivityPanel');
        if (!this.panel) {
            console.warn('Agent Activity Panel not found');
        }
    },
    
    /**
     * Show the activity panel
     */
    show() {
        if (this.panel) {
            this.panel.style.display = 'flex';
        }
    },
    
    /**
     * Hide the activity panel
     */
    hide() {
        if (this.panel) {
            this.panel.style.display = 'none';
        }
    },
    
    /**
     * Update agent state
     * @param {string} state - IDLE, UNDERSTANDING, PLANNING, EXECUTING, WAITING, COMPLETED, FAILED
     * @param {string} message - State description
     */
    setState(state, message = '') {
        this.currentState = state;
        this.show();
        
        const stateIcons = {
            'IDLE': '💤',
            'UNDERSTANDING': '🔍',
            'PLANNING': '📋',
            'EXECUTING': '⚙️',
            'WAITING': '⏳',
            'COMPLETED': '✅',
            'FAILED': '❌'
        };
        
        const icon = document.getElementById('agentStateIcon');
        const value = document.getElementById('agentStateValue');
        
        if (icon) icon.textContent = stateIcons[state] || '🤖';
        if (value) value.textContent = state;
        
        // Show typing indicator for active states
        if (['UNDERSTANDING', 'PLANNING', 'EXECUTING'].includes(state)) {
            this.showTypingIndicator(message);
        } else {
            this.hideTypingIndicator();
        }
        
        // Auto-hide on completion
        if (state === 'COMPLETED') {
            setTimeout(() => this.hide(), 3000);
        }
    },
    
    /**
     * Add or update a progress step
     * @param {number} stepNumber - Step number (1-based)
     * @param {string} title - Step title
     * @param {string} status - pending, active, completed
     * @param {string} time - Time taken or ETA
     */
    addStep(stepNumber, title, status = 'pending', time = '') {
        const container = document.getElementById('agentProgressSteps');
        if (!container) return;
        
        const stepId = `agent-step-${stepNumber}`;
        let stepEl = document.getElementById(stepId);
        
        if (!stepEl) {
            stepEl = document.createElement('div');
            stepEl.id = stepId;
            stepEl.className = `agent-step ${status}`;
            stepEl.innerHTML = `
                <div class="agent-step-icon">
                    ${status === 'completed' ? '✓' : status === 'active' ? '⚙' : stepNumber}
                </div>
                <div class="agent-step-content">
                    <div class="agent-step-title">${title}</div>
                    <div class="agent-step-time">${time}</div>
                </div>
            `;
            container.appendChild(stepEl);
        } else {
            // Update existing step
            stepEl.className = `agent-step ${status}`;
            stepEl.querySelector('.agent-step-icon').textContent = 
                status === 'completed' ? '✓' : status === 'active' ? '⚙' : stepNumber;
            stepEl.querySelector('.agent-step-title').textContent = title;
            stepEl.querySelector('.agent-step-time').textContent = time;
        }
        
        this.steps[stepNumber - 1] = { title, status, time };
    },
    
    /**
     * Clear all steps
     */
    clearSteps() {
        const container = document.getElementById('agentProgressSteps');
        if (container) {
            container.innerHTML = '';
        }
        this.steps = [];
    },
    
    /**
     * Update progress bar
     * @param {number} percentage - 0-100
     * @param {string} label - Progress label
     * @param {string} eta - Estimated time remaining
     */
    updateProgress(percentage, label = '', eta = '') {
        const container = document.getElementById('agentProgressContainer');
        const fill = document.getElementById('agentProgressFill');
        const labelEl = document.getElementById('agentProgressLabel');
        const percentageEl = document.getElementById('agentProgressPercentage');
        const etaEl = document.getElementById('agentProgressEta');
        
        if (container) container.style.display = percentage > 0 ? 'block' : 'none';
        if (fill) fill.style.width = `${percentage}%`;
        if (labelEl) labelEl.textContent = label;
        if (percentageEl) percentageEl.textContent = `${Math.round(percentage)}%`;
        if (etaEl) etaEl.textContent = eta ? `ETA: ${eta}` : '';
    },
    
    /**
     * Set current action
     * @param {string} action - Current action description
     */
    setCurrentAction(action) {
        const container = document.getElementById('agentCurrentAction');
        const text = document.getElementById('agentActionText');
        
        if (container && text) {
            container.style.display = action ? 'block' : 'none';
            text.textContent = action;
        }
    },
    
    /**
     * Set next step
     * @param {string} next - Next step description
     */
    setNextStep(next) {
        const container = document.getElementById('agentNextStep');
        const text = document.getElementById('agentNextText');
        
        if (container && text) {
            container.style.display = next ? 'block' : 'none';
            text.textContent = next;
        }
    },
    
    /**
     * Show typing indicator
     * @param {string} message - Optional message to show
     */
    showTypingIndicator(message = '') {
        const indicator = document.getElementById('typingIndicator');
        const text = document.getElementById('typingText');
        
        if (indicator) {
            indicator.style.display = 'block';
            if (text && message) {
                text.textContent = message;
            }
        }
    },
    
    /**
     * Hide typing indicator
     */
    hideTypingIndicator() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) {
            indicator.style.display = 'none';
        }
    },
    
    /**
     * Show cancel button
     */
    showCancelButton() {
        const btn = document.getElementById('agentCancelBtn');
        if (btn) btn.style.display = 'block';
    },
    
    /**
     * Hide cancel button
     */
    hideCancelButton() {
        const btn = document.getElementById('agentCancelBtn');
        if (btn) btn.style.display = 'none';
    },
    
    /**
     * Reset panel to initial state
     */
    reset() {
        this.setState('IDLE');
        this.clearSteps();
        this.updateProgress(0);
        this.setCurrentAction('');
        this.setNextStep('');
        this.hideTypingIndicator();
        this.hideCancelButton();
    },
    
    /**
     * Simulate a multi-step process (for demo/testing)
     * @param {Array} steps - Array of step objects {title, duration}
     */
    async simulateProcess(steps) {
        this.reset();
        this.setState('UNDERSTANDING', 'Analyzing your request...');
        
        for (let i = 0; i < steps.length; i++) {
            const step = steps[i];
            const stepNum = i + 1;
            
            // Add step as pending
            this.addStep(stepNum, step.title, 'pending');
            
            // Wait a bit
            await new Promise(resolve => setTimeout(resolve, 500));
            
            // Mark as active
            this.addStep(stepNum, step.title, 'active', 'In progress...');
            this.setState('EXECUTING', step.title);
            this.updateProgress((stepNum / steps.length) * 100, step.title, `~${step.duration}s`);
            
            // Simulate work
            await new Promise(resolve => setTimeout(resolve, step.duration * 1000));
            
            // Mark as completed
            this.addStep(stepNum, step.title, 'completed', `Completed in ${step.duration}s`);
        }
        
        this.setState('COMPLETED', 'All done!');
        this.updateProgress(100, 'Completed');
    }
};

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => AgentActivityController.init());
} else {
    AgentActivityController.init();
}

// Export for use in aiApp
window.AgentActivityController = AgentActivityController;
