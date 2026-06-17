{{-- Agent Activity Panel - Production-Grade UX Component --}}
<div id="agentActivityPanel" class="agent-activity-panel" style="display: none;">
    <div class="agent-activity-header">
        <div class="agent-activity-icon">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
        </div>
        <div class="agent-activity-title">Agent Activity</div>
        <button class="agent-activity-close" onclick="aiApp.hideActivityPanel()">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    
    <div class="agent-activity-body">
        {{-- Current State --}}
        <div class="agent-state-display">
            <div class="agent-state-icon" id="agentStateIcon">🔍</div>
            <div class="agent-state-text">
                <div class="agent-state-label">Status</div>
                <div class="agent-state-value" id="agentStateValue">IDLE</div>
            </div>
        </div>
        
        {{-- Progress Steps --}}
        <div class="agent-progress-steps" id="agentProgressSteps">
            <!-- Steps will be dynamically added here -->
        </div>
        
        {{-- Progress Bar --}}
        <div class="agent-progress-bar-container" id="agentProgressContainer" style="display: none;">
            <div class="agent-progress-info">
                <span class="agent-progress-label" id="agentProgressLabel">Processing...</span>
                <span class="agent-progress-percentage" id="agentProgressPercentage">0%</span>
            </div>
            <div class="agent-progress-bar">
                <div class="agent-progress-fill" id="agentProgressFill" style="width: 0%"></div>
            </div>
            <div class="agent-progress-eta" id="agentProgressEta"></div>
        </div>
        
        {{-- Current Action --}}
        <div class="agent-current-action" id="agentCurrentAction" style="display: none;">
            <div class="agent-action-label">Current Action</div>
            <div class="agent-action-text" id="agentActionText"></div>
        </div>
        
        {{-- Next Step --}}
        <div class="agent-next-step" id="agentNextStep" style="display: none;">
            <div class="agent-next-label">Next</div>
            <div class="agent-next-text" id="agentNextText"></div>
        </div>
    </div>
    
    <div class="agent-activity-footer">
        <button class="agent-cancel-btn" onclick="aiApp.cancelCurrentTask()" id="agentCancelBtn" style="display: none;">
            Cancel Task
        </button>
    </div>
</div>

{{-- Typing Indicator --}}
<div id="typingIndicator" class="typing-indicator" style="display: none;">
    <div class="ai-message-row">
        <div class="ai-message-content">
            <div class="ai-message-avatar assistant">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                </svg>
            </div>
            <div class="ai-message-body">
                <div class="ai-message-header">
                    <span class="ai-message-sender">Mom AI</span>
                </div>
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="typing-text" id="typingText"></div>
            </div>
        </div>
    </div>
</div>

<style>
/* Agent Activity Panel Styles */
.agent-activity-panel {
    position: fixed;
    bottom: 100px;
    right: 24px;
    width: 360px;
    max-height: 500px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    z-index: 1000;
    display: flex;
    flex-direction: column;
    animation: slideInUp 0.3s ease;
}

@keyframes slideInUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.agent-activity-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    border-bottom: 1px solid #E5E7EB;
}

.agent-activity-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.agent-activity-title {
    flex: 1;
    font-size: 0.938rem;
    font-weight: 600;
    color: #1F2937;
}

.agent-activity-close {
    width: 24px;
    height: 24px;
    border: none;
    background: transparent;
    color: #6B7280;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.2s;
}

.agent-activity-close:hover {
    background: #F3F4F6;
    color: #1F2937;
}

.agent-activity-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
}

/* State Display */
.agent-state-display {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #F9FAFB;
    border-radius: 8px;
    margin-bottom: 16px;
}

.agent-state-icon {
    font-size: 1.5rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

.agent-state-text {
    flex: 1;
}

.agent-state-label {
    font-size: 0.75rem;
    color: #6B7280;
    margin-bottom: 2px;
}

.agent-state-value {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1F2937;
}

/* Progress Steps */
.agent-progress-steps {
    margin-bottom: 16px;
}

.agent-step {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 8px 0;
    position: relative;
}

.agent-step:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 11px;
    top: 32px;
    width: 2px;
    height: calc(100% - 8px);
    background: #E5E7EB;
}

.agent-step.completed::after {
    background: #10B981;
}

.agent-step.active::after {
    background: #3B82F6;
}

.agent-step-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    flex-shrink: 0;
    z-index: 1;
}

.agent-step.pending .agent-step-icon {
    background: #F3F4F6;
    color: #9CA3AF;
}

.agent-step.active .agent-step-icon {
    background: #3B82F6;
    color: white;
    animation: spin 1s linear infinite;
}

.agent-step.completed .agent-step-icon {
    background: #10B981;
    color: white;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.agent-step-content {
    flex: 1;
    padding-top: 2px;
}

.agent-step-title {
    font-size: 0.813rem;
    font-weight: 500;
    color: #1F2937;
    margin-bottom: 2px;
}

.agent-step.pending .agent-step-title {
    color: #9CA3AF;
}

.agent-step-time {
    font-size: 0.75rem;
    color: #6B7280;
}

/* Progress Bar */
.agent-progress-bar-container {
    margin-bottom: 16px;
}

.agent-progress-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.agent-progress-label {
    font-size: 0.813rem;
    color: #1F2937;
}

.agent-progress-percentage {
    font-size: 0.813rem;
    font-weight: 600;
    color: #3B82F6;
}

.agent-progress-bar {
    height: 6px;
    background: #E5E7EB;
    border-radius: 3px;
    overflow: hidden;
}

.agent-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3B82F6 0%, #8B5CF6 100%);
    transition: width 0.3s ease;
}

.agent-progress-eta {
    font-size: 0.75rem;
    color: #6B7280;
    margin-top: 4px;
}

/* Current Action & Next Step */
.agent-current-action,
.agent-next-step {
    padding: 12px;
    background: #F9FAFB;
    border-radius: 8px;
    margin-bottom: 12px;
}

.agent-action-label,
.agent-next-label {
    font-size: 0.75rem;
    color: #6B7280;
    margin-bottom: 4px;
}

.agent-action-text,
.agent-next-text {
    font-size: 0.813rem;
    color: #1F2937;
    line-height: 1.5;
}

/* Footer */
.agent-activity-footer {
    padding: 12px 16px;
    border-top: 1px solid #E5E7EB;
}

.agent-cancel-btn {
    width: 100%;
    padding: 8px 16px;
    background: #FEE2E2;
    color: #DC2626;
    border: none;
    border-radius: 6px;
    font-size: 0.813rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.agent-cancel-btn:hover {
    background: #FEF2F2;
}

/* Typing Indicator */
.typing-indicator {
    padding: 0 24px;
}

.typing-dots {
    display: flex;
    gap: 4px;
    padding: 12px 0;
}

.typing-dots span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #9CA3AF;
    animation: typingBounce 1.4s infinite;
}

.typing-dots span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-dots span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typingBounce {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
}

.typing-text {
    font-size: 0.875rem;
    color: #6B7280;
    font-style: italic;
}
</style>
