@extends('layouts.backend')

@push('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    /* Enterprise Design System */
    :root {
        --ai-primary: #3B82F6;
        --ai-primary-hover: #2563EB;
        --ai-bg-primary: #FFFFFF;
        --ai-bg-secondary: #F8F9FA;
        --ai-bg-sidebar: #F3F4F6;
        --ai-text-primary: #1F2937;
        --ai-text-secondary: #6B7280;
        --ai-text-muted: #9CA3AF;
        --ai-border: #E5E7EB;
        --ai-shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
        --ai-shadow-md: 0 4px 6px rgba(0,0,0,0.07);
        --ai-shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
        --ai-radius: 8px;
        --ai-radius-lg: 12px;
    }

    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    /* Main Container */
    .ai-container {
        display: flex;
        height: calc(100vh - 60px);
        background: var(--ai-bg-secondary);
        overflow: hidden;
    }

    /* Chat History Sidebar */
    .ai-history-panel {
        width: 280px;
        background: var(--ai-bg-sidebar);
        border-right: 1px solid var(--ai-border);
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .ai-history-panel.collapsed {
        width: 0;
        min-width: 0;
        border-right: none;
        overflow: hidden;
    }
    
    /* Floating Sidebar Toggle Removed */

    .ai-history-header {
        padding: 16px;
        padding-top: 24px;
        border-bottom: 1px solid var(--ai-border);
    }

    .ai-new-chat-btn {
        width: 100%;
        padding: 12px 16px;
        background: var(--ai-bg-primary);
        border: 1px solid var(--ai-border);
        border-radius: var(--ai-radius);
        color: var(--ai-text-primary);
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .ai-new-chat-btn:hover {
        background: var(--ai-bg-secondary);
        border-color: var(--ai-primary);
    }

    .ai-history-list {
        flex: 1;
        overflow-y: auto;
        padding: 8px;
    }

    .ai-history-group {
        margin-bottom: 16px;
    }

    .ai-history-group-title {
        padding: 8px 12px;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--ai-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .ai-history-item {
        padding: 10px 12px;
        margin: 2px 0;
        border-radius: 6px;
        color: var(--ai-text-primary);
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        position: relative;
    }

    .ai-history-item:hover {
        background: var(--ai-bg-secondary);
        color: var(--ai-text-primary);
    }

    .ai-history-item.active {
        background: #E0E7FF;
        color: var(--ai-primary);
        font-weight: 500;
    }

    .ai-history-item-actions {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        display: flex;
        gap: 4px;
        opacity: 0; /* Hidden by default */
        transition: opacity 0.2s;
    }
    
    .ai-history-item.active .ai-history-item-actions,
    .ai-history-item:hover .ai-history-item-actions {
        opacity: 1; /* Show only on active or hover */
    }
    
    .ai-history-item-delete-btn {
        padding: 4px;
        background: transparent;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        color: var(--ai-text-muted);
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .ai-history-item-delete-btn:hover {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
    }

    .ai-history-item:hover .ai-history-item-actions {
        opacity: 1;
    }

    /* Search Bar Styles */
    .ai-history-search-wrapper {
        padding: 12px 16px;
        border-bottom: 1px solid var(--ai-border);
    }

    .ai-search-input-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .ai-search-input {
        width: 100%;
        padding: 8px 12px 8px 32px;
        background: var(--ai-bg-primary);
        border: 1px solid var(--ai-border);
        border-radius: 6px;
        font-size: 0.813rem;
        color: var(--ai-text-primary);
        outline: none;
        transition: all 0.2s;
    }

    .ai-search-input:focus {
        border-color: var(--ai-primary);
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }

    .ai-search-icon {
        position: absolute;
        left: 10px;
        color: var(--ai-text-muted);
        pointer-events: none;
    }

    /* Archive Toggle Styles */
    .ai-archive-toggle-wrapper {
        padding: 8px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.75rem;
        color: var(--ai-text-secondary);
        border-bottom: 1px solid var(--ai-border);
        background: rgba(0,0,0,0.02);
    }

    .ai-archive-toggle-btn {
        background: transparent;
        border: none;
        color: var(--ai-primary);
        font-weight: 500;
        cursor: pointer;
        padding: 2px 4px;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .ai-archive-toggle-btn:hover {
        background: rgba(59, 130, 246, 0.1);
    }

    /* Rename Input */
    .ai-rename-input {
        width: 100%;
        background: white;
        border: 1px solid var(--ai-primary);
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 0.875rem;
        color: var(--ai-text-primary);
        outline: none;
    }

    /* Main Chat Area */
    .ai-chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: var(--ai-bg-primary);
        position: relative;
        padding-top: 16px; /* Add top spacing */
    }

    .ai-chat-header {
        padding: 12px 24px;
        border-bottom: 1px solid var(--ai-border);
        background: var(--ai-bg-primary);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0; /* Prevent header from shrinking */
    }

    .ai-chat-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .ai-header-toggle-btn {
        padding: 8px;
        background: transparent;
        border: 1px solid var(--ai-border);
        border-radius: 6px;
        color: var(--ai-text-secondary);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .ai-header-toggle-btn:hover {
        background: var(--ai-bg-secondary);
        color: var(--ai-text-primary);
    }
    
    .ai-header-toggle-btn svg {
        transition: transform 0.3s;
    }
    
    .ai-header-toggle-btn.collapsed svg {
        transform: rotate(180deg);
    }
    
    .ai-title-wrapper {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .ai-avatar {
        width: 36px;
        height: 36px;
        border-radius: 6px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .ai-chat-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--ai-text-primary);
        margin: 0;
    }



    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* Voice Mode Toggle Styles */
    .voice-toggle-switch {
        position: relative;
        display: inline-block;
        width: 52px;
        height: 28px;
    }

    .voice-toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .voice-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #374151; /* Dark gray for off state */
        border-radius: 34px;
        transition: .4s;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 6px;
        border: 1px solid #4B5563;
    }

    .voice-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 4px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }

    input:checked + .voice-slider {
        background-color: #10B981; /* Green for ON */
        border-color: #059669;
        box-shadow: 0 0 10px rgba(16, 185, 129, 0.4);
    }

    input:checked + .voice-slider:before {
        transform: translateX(24px);
    }

    .voice-icon-on, .voice-icon-off {
        font-size: 10px;
        color: white;
        z-index: 10;
        transition: opacity 0.3s;
    }

    .voice-icon-on {
        opacity: 0;
        margin-left: 4px;
    }

    .voice-icon-off {
        opacity: 1;
        margin-right: 4px;
    }

    input:checked + .voice-slider .voice-icon-on {
        opacity: 1;
    }

    input:checked + .voice-slider .voice-icon-off {
        opacity: 0;
    }

    /* Pulse animation when Voice Active */
    .voice-active-halo {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: voice-pulse 1.5s infinite cubic-bezier(0.66, 0, 0, 1);
    }

    @keyframes voice-pulse {
        to {
            box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
        }
    }

    /* Messages Area */
    .ai-messages-container {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
        scroll-behavior: smooth;
    }

    .ai-messages-container::-webkit-scrollbar {
        width: 6px;
    }

    .ai-messages-container::-webkit-scrollbar-thumb {
        background: var(--ai-border);
        border-radius: 3px;
    }

    /* Empty State */
    .ai-empty-state {
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 48px 24px;
    }

    .ai-empty-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--ai-bg-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin-bottom: 24px;
        box-shadow: var(--ai-shadow-sm);
    }

    .ai-empty-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--ai-text-primary);
        margin-bottom: 12px;
    }

    .ai-empty-subtitle {
        font-size: 0.938rem;
        color: var(--ai-text-secondary);
        margin-bottom: 32px;
        max-width: 400px;
    }

    .ai-quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
        max-width: 800px;
        width: 100%;
    }

    .ai-quick-action {
        padding: 16px;
        background: var(--ai-bg-primary);
        border: 1px solid var(--ai-border);
        border-radius: var(--ai-radius);
        text-align: left;
        cursor: pointer;
        transition: all 0.2s;
    }

    .ai-quick-action:hover {
        border-color: var(--ai-primary);
        box-shadow: var(--ai-shadow-md);
        transform: translateY(-2px);
    }

    .ai-quick-action-title {
        font-size: 0.813rem;
        font-weight: 600;
        color: var(--ai-text-primary);
        margin-bottom: 4px;
    }

    .ai-quick-action-desc {
        font-size: 0.75rem;
        color: var(--ai-text-secondary);
    }

    /* Message Bubbles */
    .ai-message-row {
        display: flex;
        margin-bottom: 24px;
        width: 100%;
        justify-content: center;
    }

    .ai-message-content {
        width: 100%;
        max-width: 800px;
        display: flex;
        gap: 12px;
    }

    .ai-message-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
    }

    .ai-message-avatar.user {
        background: #E0E7FF;
        color: var(--ai-primary);
    }

    .ai-message-avatar.assistant {
        background: transparent;
        color: white;
    }

    .ai-message-body {
        flex: 1;
        padding-top: 4px;
    }

    .ai-message-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }

    .ai-message-sender {
        font-size: 0.813rem;
        font-weight: 600;
        color: var(--ai-text-primary);
    }

    .ai-message-time {
        font-size: 0.75rem;
        color: var(--ai-text-muted);
    }

    .ai-message-text {
        font-size: 0.938rem;
        line-height: 1.6;
        color: var(--ai-text-primary);
    }

    .ai-message-text p {
        margin: 0 0 12px 0;
    }

    .ai-message-text p:last-child {
        margin-bottom: 0;
    }

    .ai-message-text strong {
        font-weight: 600;
    }

    .ai-message-text table {
        width: 100%;
        border-collapse: collapse;
        margin: 12px 0;
        font-size: 0.875rem;
    }

    .ai-message-text th,
    .ai-message-text td {
        padding: 8px 12px;
        border: 1px solid var(--ai-border);
        text-align: left;
    }

    .ai-message-text th {
        background: var(--ai-bg-secondary);
        font-weight: 600;
    }

    .ai-message-actions {
        margin-top: 8px;
        display: flex;
        gap: 8px;
    }

    .ai-message-action-btn {
        padding: 4px 8px;
        font-size: 0.75rem;
        color: var(--ai-text-secondary);
        background: transparent;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .ai-message-action-btn:hover {
        background: var(--ai-bg-secondary);
        color: var(--ai-text-primary);
    }

    /* Typing Indicator */
    .ai-typing {
        display: flex;
        gap: 4px;
        padding: 12px 0;
    }

    .ai-typing-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--ai-text-muted);
        animation: typing 1.4s infinite ease-in-out;
    }

    .ai-typing-dot:nth-child(1) { animation-delay: -0.32s; }
    .ai-typing-dot:nth-child(2) { animation-delay: -0.16s; }

    @keyframes typing {
        0%, 80%, 100% { transform: scale(0); opacity: 0.5; }
        40% { transform: scale(1); opacity: 1; }
    }

    /* Input Bar */
    .ai-input-container {
        padding: 16px 24px;
        background: var(--ai-bg-primary);
        border-top: 1px solid var(--ai-border);
    }

    .ai-input-wrapper {
        max-width: 800px;
        margin: 0 auto;
        position: relative;
    }

    .ai-input-box {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        padding: 12px 16px;
        background: var(--ai-bg-primary);
        border: 1px solid var(--ai-border);
        border-radius: var(--ai-radius-lg);
        box-shadow: var(--ai-shadow-sm);
        transition: all 0.2s;
    }

    .ai-input-box:focus-within {
        border-color: var(--ai-primary);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .ai-input {
        flex: 1;
        border: none;
        outline: none;
        font-size: 0.938rem;
        color: var(--ai-text-primary);
        resize: none;
        max-height: 200px;
        min-height: 24px;
        line-height: 1.5;
        font-family: inherit;
    }

    .ai-input::placeholder {
        color: var(--ai-text-muted);
    }

    .ai-send-btn {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        background: var(--ai-primary);
        border: none;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .ai-send-btn:hover:not(:disabled) {
        background: var(--ai-primary-hover);
        transform: translateY(-1px);
    }

    .ai-send-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .ai-input-footer {
        text-align: center;
        margin-top: 8px;
    }

    .ai-input-hint {
        font-size: 0.75rem;
        color: var(--ai-text-muted);
    }

    /* Skeleton Loaders */
    .ai-skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .ai-skeleton-item {
        height: 48px;
        border-radius: 6px;
        margin: 4px 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .ai-history-panel {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 100;
            transform: translateX(-100%);
        }

        .ai-history-panel.open {
            transform: translateX(0);
        }
    }

    /* Inline Status (Copilot-style) */
    .ai-inline-status {
        font-size: 13px;
        color: #6b7280;
        margin-top: 8px;
        margin-left: 52px; /* Align with typing indicator */
        padding: 4px 0;
    }

    .ai-inline-status .status-text {
        font-weight: 400;
    }

    /* Pending message indicator */
    .ai-message-pending .ai-message-text {
        opacity: 0.7;
        font-style: italic;
    }

    /* --- GENERATIVE UI SYSTEM --- */
    
    /* 1. Data Tables */
    .ai-response-table-wrapper {
        overflow-x: auto;
        margin: 12px 0;
        border-radius: var(--ai-radius);
        border: 1px solid var(--ai-border);
    }
    
    .ai-response-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
        background: white;
    }
    
    .ai-response-table th {
        background: #F9FAFB;
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        color: var(--ai-text-secondary);
        border-bottom: 1px solid var(--ai-border);
        white-space: nowrap;
    }
    
    .ai-response-table td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--ai-border);
        color: var(--ai-text-primary);
    }
    
    .ai-response-table tr:last-child td {
        border-bottom: none;
    }
    
    .ai-response-table tr:nth-child(even) {
        background: #F3F4F6; /* Zebra striping */
    }
    
    /* 2. Policy / RAG (Blockquotes) */
    .ai-response-policy {
        margin: 12px 0;
        padding: 16px;
        background: #F8FAFC;
        border-left: 4px solid var(--ai-primary);
        border-radius: 0 var(--ai-radius) var(--ai-radius) 0;
        font-style: italic;
        color: #475569;
    }
    
    .ai-response-policy-source {
        display: block;
        margin-top: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--ai-primary);
        font-style: normal;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* 3. Email / Drafts */
    .ai-response-email {
        margin: 16px 0;
        background: white;
        border: 1px solid var(--ai-border);
        border-radius: var(--ai-radius);
        box-shadow: var(--ai-shadow-sm);
        overflow: hidden;
    }
    
    .ai-email-header {
        padding: 12px 16px;
        background: #F3F4F6;
        border-bottom: 1px solid var(--ai-border);
        font-weight: 600;
        color: var(--ai-text-primary);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .ai-email-body {
        padding: 16px;
        font-family: 'Courier New', Courier, monospace;
        white-space: pre-wrap;
        color: var(--ai-text-primary);
        font-size: 0.9rem;
    }
    
    /* 4. Action Confirmation */
    .ai-response-success {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: #ECFDF5;
        border: 1px solid #A7F3D0;
        border-radius: var(--ai-radius);
        color: #065F46;
        font-weight: 500;
        font-size: 0.875rem;
        margin: 8px 0;
    }
    
    .ai-response-success svg {
        width: 16px;
        height: 16px;
        color: #10B981;
    }
    
    /* 5. Error State */
    .ai-response-error {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: #FEF2F2;
        border: 1px solid #FECACA;
        border-radius: var(--ai-radius);
        color: #991B1B;
        font-size: 0.875rem;
        margin: 8px 0;
    }
    
    .ai-retry-btn {
        margin-left: 8px;
        padding: 2px 8px;
        background: white;
        border: 1px solid #FECACA;
        border-radius: 4px;
        font-size: 0.75rem;
        cursor: pointer;
        color: #991B1B;
    }
    
    .ai-retry-btn:hover {
        background: #FEE2E2;
    }

    /* Markdown Overrides */
    .ai-message-text h1, .ai-message-text h2, .ai-message-text h3 {
        margin-top: 16px;
        margin-bottom: 8px;
        font-weight: 600;
        color: #111827;
    }
    .ai-message-text h1 { font-size: 1.25rem; }
    .ai-message-text h2 { font-size: 1.1rem; }
    .ai-message-text h3 { font-size: 1rem; }
    
    .ai-message-text ul, .ai-message-text ol {
        margin: 8px 0;
        padding-left: 24px;
    }
    .ai-message-text li {
        margin-bottom: 4px;
    }
    
    .ai-message-text p {
        margin-bottom: 12px;
    }
    
    /* Placeholder Animation */
    @keyframes pulse-bg {
        0% { opacity: 0.6; }
        50% { opacity: 0.3; }
        100% { opacity: 0.6; }
    }
    .ai-placeholder-text {
        color: var(--ai-text-muted);
        animation: pulse-bg 1.5s infinite;
    }

    /* Toast Notification */
    .ai-toast {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: #1F2937;
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        z-index: 1000;
        font-size: 0.9rem;
        pointer-events: none;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .ai-toast.error { background: #EF4444; }
    .ai-toast.success { background: #10B981; }

    .ai-toast.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper" style="padding: 0;">
    <div class="ai-container">
        
        <!-- Chat History Sidebar -->
        <div class="ai-history-panel" id="aiHistoryPanel">
            <div class="ai-history-header">
                <button class="ai-new-chat-btn" onclick="aiApp.createNewConversation()">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Chat
                </button>
            </div>

            <div class="ai-history-search-wrapper">
                <div class="ai-search-input-group">
                    <svg class="ai-search-icon" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" class="ai-search-input" id="aiHistorySearch" placeholder="Search chats..." oninput="aiApp.handleSearch(this.value)">
                </div>
            </div>

            <div class="ai-archive-toggle-wrapper">
                <span id="aiViewLabel">Active Chats</span>
                <button class="ai-archive-toggle-btn" id="aiArchiveToggle" onclick="aiApp.toggleArchiveView()">
                    View Archive
                </button>
            </div>

            <div class="ai-history-list" id="aiHistoryList">
                <!-- Loading skeleton -->
                <div class="ai-skeleton ai-skeleton-item"></div>
                <div class="ai-skeleton ai-skeleton-item"></div>
                <div class="ai-skeleton ai-skeleton-item"></div>
            </div>
        </div>
        
        <!-- Main Chat Area -->
        <div class="ai-chat-main">
            <div class="ai-chat-header">
                <div class="ai-chat-header-left">
                    <button class="ai-header-toggle-btn" onclick="aiApp.toggleSidebar()" title="Toggle sidebar">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <div class="ai-title-wrapper">
                        <h3 class="ai-chat-title">Workpilot AI</h3>
                        <div class="ai-chat-status">
                            <span class="ai-status-dot"></span>
                            Online
                        </div>
                    </div>
                </div>

                <!-- Voice Mode Toggle -->
                <div class="voice-mode-wrapper ms-auto d-flex align-items-center gap-2">
                    <select id="voiceLanguage" class="form-select form-select-sm border-0 bg-light text-muted fw-bold" style="width: auto; font-size: 0.7rem; padding-right: 2rem; cursor: pointer;">
                        <option value="en-US">🇺🇸 English</option>
                        <option value="hi-IN">🇮🇳 Hindi (हिन्दी)</option>
                        <option value="es-ES">🇪🇸 Spain (Español)</option>
                        <option value="fr-FR">🇫🇷 France (Français)</option>
                        <option value="de-DE">🇩🇪 German (Deutsch)</option>
                        <option value="ja-JP">🇯🇵 Japanese (日本語)</option>
                    </select>
                    <span class="text-xs text-muted fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Voice Mode</span>
                    <label class="voice-toggle-switch">
                        <input type="checkbox" id="voiceModeToggle">
                        <span class="voice-slider">
                            <i class="fas fa-microphone voice-icon-on"></i>
                            <i class="fas fa-microphone-slash voice-icon-off"></i>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Messages -->
            <div class="ai-messages-container" id="aiMessages">
                <!-- Empty State -->
                <div class="ai-empty-state" id="aiEmptyState">
                    <div class="ai-empty-icon">
                        <img src="{{ asset('modules/agenticai/images/Chatbot Chat Message.jpg') }}" alt="Workpilot AI" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                    </div>
                    <h2 class="ai-empty-title">Hi {{ auth()->check() ? (auth()->user()->first_name ?? auth()->user()->name) : 'there' }} 👋</h2>
                    <p class="ai-empty-subtitle">I'm Workpilot AI, your HR assistant. I can help you with leaves, attendance, approvals, and more.</p>
                    
                    <div class="ai-quick-actions">
                        <button class="ai-quick-action" onclick="aiApp.sendQuickMessage('Show my leave balance')">
                            <div class="ai-quick-action-title">📅 Leaves</div>
                            <div class="ai-quick-action-desc">Show my leave balance</div>
                        </button>
                        <button class="ai-quick-action" onclick="aiApp.sendQuickMessage('What is the remote work policy?')">
                            <div class="ai-quick-action-title"> Policy</div>
                            <div class="ai-quick-action-desc">What is the remote work policy?</div>
                        </button>
                        <button class="ai-quick-action" onclick="aiApp.sendQuickMessage('Apply for sick leave')">
                            <div class="ai-quick-action-title">🤒 Apply Leave</div>
                            <div class="ai-quick-action-desc">Apply for sick leave</div>
                        </button>
                        <button class="ai-quick-action" onclick="aiApp.sendQuickMessage('Draft an announcement')">
                            <div class="ai-quick-action-title"> Draft</div>
                            <div class="ai-quick-action-desc">Draft an announcement</div>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Inline Status (Copilot-style) --}}
            <div id="aiInlineStatus" class="ai-inline-status" style="display: none;">
                <span class="status-text">Workpilot AI · <span id="statusMessage">Checking…</span></span>
            </div>

            <!-- Drop Zone Overlay -->
            <div id="aiDropZone" class="ai-drop-zone">
                <div class="ai-drop-zone-content">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    <span>Drop files here to upload</span>
                </div>
            </div>

            <!-- Input Bar -->
            <div class="ai-input-container">
                <!-- File Preview (Hidden by default) -->
                <div id="aiFilePreview" class="ai-file-preview" style="display: none;">
                    <span class="ai-file-icon">📎</span>
                    <span id="aiFileName" class="ai-file-name">filename.pdf</span>
                    <button class="ai-file-remove" onclick="aiApp.clearAttachment()">✕</button>
                    <!-- Hidden input to store URL -->
                    <input type="hidden" id="aiAttachmentUrl">
                </div>

                <div class="ai-input-wrapper">
                    <div class="ai-input-box">
                        <textarea 
                            id="aiInput" 
                            class="ai-input" 
                            placeholder="Message Workpilot AI..."
                            rows="1"
                            onkeydown="aiApp.handleKeyPress(event)"
                            oninput="aiApp.autoResize(this)"></textarea>
                        
                        <!-- File Upload Trigger -->
                        <input type="file" id="aiFileInput" style="display: none;" onchange="aiApp.handleFileSelect(this)">
                        <button class="ai-upload-btn" onclick="document.getElementById('aiFileInput').click()" title="Attach file">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                        </button>

                        <button class="ai-send-btn" id="aiSendBtn" onclick="aiApp.sendMessage()" disabled>
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="ai-input-footer">
                        <span class="ai-input-hint">Workpilot AI can make mistakes. Consider checking important info.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast Notification Container -->
    <div id="aiToast" class="ai-toast"></div>
</div>

<style>
/* ... Valid Existing CSS ... */

/* Drop Zone */
.ai-drop-zone {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(59, 130, 246, 0.1);
    backdrop-filter: blur(2px);
    border: 2px dashed #3B82F6;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
}

.ai-drop-zone.active {
    opacity: 1;
    pointer-events: all;
}

.ai-drop-zone-content {
    background: white;
    padding: 24px;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    color: #3B82F6;
    font-weight: 600;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

/* File Preview Chip */
.ai-file-preview {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    background: #F3F4F6;
    border: 1px solid #E5E7EB;
    border-radius: 6px;
    margin-bottom: 8px;
    font-size: 0.875rem;
}

.ai-file-name {
    color: #374151;
    font-weight: 500;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ai-file-remove {
    background: transparent;
    border: none;
    color: #9CA3AF;
    cursor: pointer;
    font-size: 1rem;
    padding: 0 4px;
}

.ai-file-remove:hover { color: #EF4444; }

/* Upload Button */
.ai-upload-btn {
    background: transparent;
    border: none;
    color: #6B7280;
    padding: 6px;
    border-radius: 6px;
    cursor: pointer;
    margin-right: 4px;
    transition: all 0.2s;
}

.ai-upload-btn:hover {
    background: #F3F4F6;
    color: #3B82F6;
}
</style>

<script>
// Enterprise AI Application
const aiApp = {
    currentConversationId: null,
    conversations: [],
    messages: [],
    isLoading: false,
    searchTerm: '',
    isArchiveView: false,
    editingId: null,

    // File Upload Methods
    currentAttachment: null,

    setupDragAndDrop() {
        const dropZone = document.getElementById('aiDropZone');
        const mainArea = document.querySelector('.ai-chat-main');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            mainArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        mainArea.addEventListener('dragenter', () => dropZone.classList.add('active'));
        dropZone.addEventListener('dragleave', (e) => {
            if (e.relatedTarget === null || !dropZone.contains(e.relatedTarget)) {
                dropZone.classList.remove('active');
            }
        });
        
        mainArea.addEventListener('drop', (e) => {
            dropZone.classList.remove('active');
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length) this.handleFILES(files[0]);
        });
    },

    handleFileSelect(input) {
        if (input.files.length) this.handleFILES(input.files[0]);
    },

    async handleFILES(file) {
        if (file.size > 10 * 1024 * 1024) {
            this.showToast('File too large (Max 10MB)', 'error');
            return;
        }

        this.showToast('Uploading...', 'success');
        const formData = new FormData();
        formData.append('file', file);
        
        try {
            const response = await fetch('/api/ai/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
                },
                body: formData
            });
            
            const data = await response.json();
            if (data.status === 'success') {
                this.currentAttachment = data;
                this.showFilePreview(data.filename);
                this.showToast('File attached', 'success');
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error(error);
            this.showToast('Upload failed', 'error');
        }
    },

    showFilePreview(name) {
        document.getElementById('aiFilePreview').style.display = 'inline-flex';
        document.getElementById('aiFileName').textContent = name;
        document.getElementById('aiSendBtn').disabled = false;
    },

    clearAttachment() {
        this.currentAttachment = null;
        document.getElementById('aiFilePreview').style.display = 'none';
        document.getElementById('aiFileInput').value = '';
    },

    init() {
        this.loadConversations();
        this.setupInputListeners();
        this.setupDragAndDrop();
        this.setupVoiceMode(); // Init Voice Mode
        
        // Check if there's a conversation ID in URL
        const urlParams = new URLSearchParams(window.location.search);
        const convId = urlParams.get('conversation');
        if (convId) {
            this.loadConversation(parseInt(convId));
        }
    },

    setupInputListeners() {
        const input = document.getElementById('aiInput');
        input.addEventListener('input', () => {
            const sendBtn = document.getElementById('aiSendBtn');
            sendBtn.disabled = !input.value.trim();
        });

        const searchInput = document.getElementById('aiHistorySearch');
        if (searchInput) {
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    searchInput.value = '';
                    this.handleSearch('');
                    searchInput.blur();
                }
            });
        }
    },

    handleSearch(term) {
        this.searchTerm = term.toLowerCase().trim();
        this.renderConversations();
    },

    toggleArchiveView() {
        this.isArchiveView = !this.isArchiveView;
        const btn = document.getElementById('aiArchiveToggle');
        const label = document.getElementById('aiViewLabel');
        
        if (this.isArchiveView) {
            btn.textContent = 'View Active';
            label.textContent = 'Archived Chats';
        } else {
            btn.textContent = 'View Archive';
            label.textContent = 'Active Chats';
        }
        
        this.loadConversations();
    },

    async loadConversations() {
        try {
            const url = this.isArchiveView ? '/api/ai/conversations?archived=1' : '/api/ai/conversations';
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
                }
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Load conversations failed:', response.status, errorText);
                return;
            }

            const data = await response.json();
            if (data.status === 'success') {
                this.conversations = data.groups;
                this.renderConversations();
            }
        } catch (error) {
            console.error('Failed to load conversations:', error);
        }
    },

    renderConversations() {
        const container = document.getElementById('aiHistoryList');
        container.innerHTML = '';

        // this.conversations is now an array of groups: [{title: '...', items: [...]}, ...]
        const groups = this.conversations || [];

        groups.forEach(group => {
            const items = group.items || [];
            if (items.length > 0) {
                const groupDiv = document.createElement('div');
                groupDiv.className = 'ai-history-group';
                groupDiv.innerHTML = `<div class="ai-history-group-title">${group.title}</div>`;

                items.forEach(conv => {
                    // Search filtering
                    if (this.searchTerm && !conv.title.toLowerCase().includes(this.searchTerm)) {
                        return;
                    }

                    const item = document.createElement('div');
                    item.className = 'ai-history-item' + (conv.id === this.currentConversationId ? ' active' : '');
                    
                    if (this.editingId === conv.id) {
                        item.innerHTML = `
                            <input type="text" class="ai-rename-input" id="rename-input-${conv.id}" 
                                value="${conv.title}" 
                                onblur="aiApp.saveRename(${conv.id})"
                                onkeydown="if(event.key === 'Enter') aiApp.saveRename(${conv.id}); if(event.key === 'Escape') aiApp.cancelRename()">
                        `;
                        setTimeout(() => document.getElementById(`rename-input-${conv.id}`).focus(), 10);
                    } else {
                        item.innerHTML = `
                            <span class="ai-conv-title">${conv.title}</span>
                            <div class="ai-history-item-actions">
                                <button class="ai-history-item-delete-btn" onclick="aiApp.startRename(${conv.id}, event)" title="Rename">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </button>
                                <button class="ai-history-item-delete-btn" onclick="aiApp.toggleArchive(${conv.id}, event)" title="${this.isArchiveView ? 'Unarchive' : 'Archive'}">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                    </svg>
                                </button>
                                <button class="ai-history-item-delete-btn" onclick="aiApp.deleteConversation(${conv.id}, event)" title="Delete">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        `;
                    }

                    item.onclick = (e) => {
                        if (!e.target.closest('.ai-history-item-actions') && !e.target.closest('.ai-rename-input')) {
                            this.loadConversation(conv.id);
                        }
                    };
                    groupDiv.appendChild(item);
                });

                container.appendChild(groupDiv);
            }
        });

        if (container.children.length === 0) {
            container.innerHTML = '<div style="padding: 24px; text-align: center; color: rgba(255,255,255,0.5); font-size: 0.875rem;">No conversations yet</div>';
        }
    },

    // Voice Mode State
    isVoiceActive: false,
    recognition: null,
    synth: window.speechSynthesis,
    isSpeaking: false,

    setupVoiceMode() {
        if (!('webkitSpeechRecognition' in window)) {
            console.warn('Speech recognition not supported');
            document.querySelector('.voice-mode-wrapper').style.display = 'none';
            return;
        }

        this.recognition = new webkitSpeechRecognition();
        this.recognition.continuous = false; // We restart manually for better control
        this.recognition.interimResults = true; // ENABLE DEEP BARGE-IN
        this.recognition.lang = 'en-US';

        this.recognition.onstart = () => {
            const dot = document.querySelector('.voice-slider');
            if (dot) dot.classList.add('voice-active-halo');
            this.showInlineStatus('Listening…');
        };

        this.recognition.onend = () => {
            const dot = document.querySelector('.voice-slider');
            if (dot) dot.classList.remove('voice-active-halo');
            
            // Auto-restart if active and not speaking and not processing
            if (this.isVoiceActive && !this.isSpeaking && !this.isLoading) {
                try {
                    this.recognition.start();
                } catch (e) {
                    // console.log('Voice restart skipped', e);
                }
            } else {
                 // If we stopped listening because we are loading, just clear status
                 if(!this.isLoading) this.hideInlineStatus();
            }
        };

        this.recognition.onresult = (event) => {
            // 1. DEEP BARGE-IN: Stop AI on ANY sound (Interim or Final)
            if (this.isSpeaking) {
                // console.log('Deep Barge-in detected: Silence!');
                if (this.currentAudio) {
                    this.currentAudio.pause();
                    this.currentAudio = null;
                }
                this.synth.cancel();
                this.isSpeaking = false;
            }

            // 2. Process Results
            let finalTranscript = '';
            for (let i = event.resultIndex; i < event.results.length; ++i) {
                if (event.results[i].isFinal) {
                    finalTranscript += event.results[i][0].transcript;
                }
            }

            // 3. Send only if final
            if (finalTranscript.trim()) {
                // console.log('Voice result (Final):', finalTranscript);
                document.getElementById('aiInput').value = finalTranscript;
                this.sendMessage(); // Auto-send
            }
        };

        this.recognition.onerror = (event) => {
            if (event.error === 'no-speech' || event.error === 'aborted') {
                // Ignore these common errors in continuous mode
                return;
            }
            console.error('Voice error', event.error);
            
            if (event.error === 'not-allowed') {
                this.isVoiceActive = false;
                document.getElementById('voiceModeToggle').checked = false;
                this.showToast('Microphone access denied');
            }
        };

        // Bind Toggle
        const toggle = document.getElementById('voiceModeToggle');
        toggle.addEventListener('change', (e) => {
            this.toggleVoiceMode(e.target.checked);
        });

        // Bind Language Selector
        const langSelect = document.getElementById('voiceLanguage');
        if (langSelect) {
            langSelect.addEventListener('change', (e) => {
                this.changeLanguage(e.target.value);
            });
        }
    },

    changeLanguage(lang) {
        if (this.recognition) {
            this.recognition.lang = lang;
            this.showToast(`Language set to ${lang}`);
            
            // Restart if active to apply new language
            if (this.isVoiceActive) {
                this.recognition.stop();
                setTimeout(() => this.tryResumeListening(), 500);
            }
        }
    },

    toggleVoiceMode(isActive) {
        this.isVoiceActive = isActive;
        
        if (isActive) {
            this.showToast('Voice Mode: ON');
            try {
                this.recognition.start();
            } catch (e) {
                console.error(e);
            }
        } else {
            this.showToast('Voice Mode: OFF');
            this.recognition.stop();
            this.synth.cancel(); // Stop speaking
            this.hideInlineStatus();
        }
    },

    async speakResponse(text) {
        if (!this.isVoiceActive || !text) return;

        // 1. CHECK FOR VOICE OFF COMMAND
        if (text.includes('<<VOICE_OFF>>')) {
            this.toggleVoiceMode(false);
            text = text.replace('<<VOICE_OFF>>', '').trim();
            if (!text) return; 
        }

        // 2. CHECK FOR LANGUAGE SWITCH COMMAND
        const langMatch = text.match(/<<LANG:([a-zA-Z-]+)>>/);
        if (langMatch) {
            const newLang = langMatch[1];
            // console.log('Language Switch Command:', newLang);
            this.changeLanguage(newLang);
            
            // Update Dropdown UI
            const langSelect = document.getElementById('voiceLanguage');
            if (langSelect) {
                langSelect.value = newLang;
            }
            
            text = text.replace(langMatch[0], '').trim();
            if (!text) return;
        }

        this.isSpeaking = true;
        // this.recognition.stop(); // ENABLE BARGE-IN: Keep listening while speaking

        // Clean text (remove markdown etc for speech)
        const cleanText = text.replace(/[*#`_]/g, '')
                             .replace(/\[.*?\]/g, '') // remove links [text]
                             .replace(/\(.*?\)/g, '') // remove links (url)
                             .replace(/<[^>]*>/g, ''); // remove html

        try {
            // OPTIMIZATION: Use GET request for direct streaming (Browser handles buffering)
            // Limit GET to ~2000 chars to be safe. Fallback to POST for long text.
            if (cleanText.length < 2000) {
                const params = new URLSearchParams({
                    text: cleanText,
                    voice: 'nova'
                });
                
                // Direct stream URL
                const audioUrl = `/api/ai/tts?${params.toString()}`;
                const audio = new Audio(audioUrl);
                this.currentAudio = audio; // Store for barge-in
                
                audio.onended = () => {
                    this.isSpeaking = false;
                    // Resume listening
                    if (this.isVoiceActive) this.tryResumeListening();
                };

                audio.onerror = (e) => {
                    console.error('Audio Stream Error', e);
                    this.isSpeaking = false;
                    this.fallbackBrowserTTS(cleanText); // Fallback
                };

                await audio.play();
                
            } else {
                // Fallback to POST for long text (Slower but safer)
                const response = await fetch('/api/ai/tts', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
                    },
                    body: JSON.stringify({ 
                        text: cleanText,
                        voice: 'nova'
                    })
                });

                if (!response.ok) throw new Error('TTS Failed');

                const blob = await response.blob();
                const audioUrl = URL.createObjectURL(blob);
                const audio = new Audio(audioUrl);
                
                audio.onended = () => {
                    this.isSpeaking = false;
                    URL.revokeObjectURL(audioUrl);
                    if (this.isVoiceActive) this.tryResumeListening();
                };

                audio.onerror = () => { throw new Error('Audio playback failed'); };
                await audio.play();
            }

        } catch (error) {
            console.error('TTS Error:', error);
            this.isSpeaking = false;
            // Fallback to browser TTS if API fails
            this.fallbackBrowserTTS(cleanText);
        }
    },

    tryResumeListening() {
        try {
            this.recognition.start();
        } catch (e) { /* ignore */ }
    },



    fallbackBrowserTTS(text) {
        const utterance = new SpeechSynthesisUtterance(text);
        // Use selected language or default to en-US
        const langCode = document.getElementById('voiceLanguage') ? document.getElementById('voiceLanguage').value : 'en-US';
        utterance.lang = langCode;
        
        // Android/Chrome optimization: Try to find a Google voice for that language
        const voices = window.speechSynthesis.getVoices();
        const googleVoice = voices.find(v => v.lang === langCode && v.name.includes('Google'));
        if (googleVoice) utterance.voice = googleVoice;

        utterance.onend = () => {
            if (this.isVoiceActive) {
                 try { this.recognition.start(); } catch (e) {}
            }
        };
        this.synth.speak(utterance);
    },

    async loadConversation(id) {
        this.currentConversationId = id;
        this.hideEmptyState();
        this.renderConversations(); // Update active state

        try {
            const response = await fetch(`/api/ai/conversations/${id}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
                }
            });

            const data = await response.json();
            if (data.status === 'success') {
                this.messages = data.messages;
                this.renderMessages();
                
                // Update URL
                window.history.pushState({}, '', `?conversation=${id}`);
            }
        } catch (error) {
            console.error('Failed to load conversation:', error);
        }
    },

    renderMessages() {
        const container = document.getElementById('aiMessages');
        container.innerHTML = '';

        this.messages.forEach(msg => {
            this.appendMessageToDOM(msg);
        });

        this.scrollToBottom();
    },

    appendMessageToDOM(message) {
        const container = document.getElementById('aiMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'ai-message-row';

        const isUser = message.sender === 'user';
        const userInitial = '{{ auth()->check() ? substr(auth()->user()->name, 0, 1) : "U" }}';
        const avatar = isUser 
            ? `<div class="ai-message-avatar user">${userInitial}</div>`
            : `<div class="ai-message-avatar assistant"><img src="{{ asset('modules/agenticai/images/Chatbot Chat Message.jpg') }}" alt="Workpilot AI" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;"></div>`;

        const senderName = isUser ? '{{ auth()->check() ? auth()->user()->name : "User" }}' : 'Workpilot AI';
        const time = new Date(message.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

        // Parse AI state from message (if it's an AI message)
        if (!isUser && message.content) {
            this.parseAndShowAgentState(message.content);
        }

        // Check for Attachment
        let attachmentHTML = '';
        if (message.metadata && message.metadata.attachment_url) {
            const url = message.metadata.attachment_url;
            const isImage = url.match(/\.(jpeg|jpg|gif|png)$/i) != null;
            
            if (isImage) {
                attachmentHTML = `
                    <div style="margin-top: 8px; margin-bottom: 8px;">
                        <img src="${url}" alt="Attachment" style="max-width: 300px; max-height: 300px; object-fit: contain; border-radius: 8px; border: 1px solid #E5E7EB; cursor: pointer;" onclick="window.open('${url}', '_blank')">
                    </div>
                `;
            } else {
                attachmentHTML = `
                    <div style="margin-top: 8px; margin-bottom: 8px;">
                        <a href="${url}" target="_blank" class="ai-file-preview" style="text-decoration: none;">
                            📎 Attached File
                        </a>
                    </div>
                `;
            }
        }

        messageDiv.innerHTML = `
            <div class="ai-message-content">
                ${avatar}
                <div class="ai-message-body">
                    <div class="ai-message-header">
                        <span class="ai-message-sender">${senderName}</span>
                        <span class="ai-message-time">${time}</span>
                    </div>
                    ${attachmentHTML}
                    <div class="ai-message-text">${this.formatMessage(this.cleanMessageContent(message.content))}</div>
                    ${!isUser ? `
                        <div class="ai-message-actions">
                            <button class="ai-message-action-btn" onclick="aiApp.copyMessage('${message.id}')">
                                <i class="far fa-copy"></i> Copy
                            </button>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;

        container.appendChild(messageDiv);
    },

    formatMessage(content) {
        // Convert markdown-like syntax
        let formatted = content
            .replace(/\n/g, '<br>')
            .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" class="text-blue-600 hover:underline">$1</a>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>');

        // Convert tables (simple markdown tables)
        if (formatted.includes('|')) {
            formatted = this.convertMarkdownTable(formatted);
        }

        return formatted;
    },

    convertMarkdownTable(text) {
        const lines = text.split('<br>');
        let inTable = false;
        let tableHTML = '';
        let result = '';

        lines.forEach((line, index) => {
            if (line.trim().startsWith('|')) {
                if (!inTable) {
                    inTable = true;
                    tableHTML = '<table>';
                }

                const cells = line.split('|').filter(cell => cell.trim());
                const isHeader = index === 0 || (index === 1 && line.includes('---'));

                if (!isHeader || index === 0) {
                    const tag = index === 0 ? 'th' : 'td';
                    tableHTML += '<tr>' + cells.map(cell => `<${tag}>${cell.trim()}</${tag}>`).join('') + '</tr>';
                }
            } else {
                if (inTable) {
                    tableHTML += '</table>';
                    result += tableHTML;
                    inTable = false;
                    tableHTML = '';
                }
                result += line + '<br>';
            }
        });

        if (inTable) {
            tableHTML += '</table>';
            result += tableHTML;
        }

        return result;
    },

    showTypingIndicator() {
        const container = document.getElementById('aiMessages');
        const typingDiv = document.createElement('div');
        typingDiv.id = 'aiTypingIndicator';
        typingDiv.className = 'ai-message-row';
        typingDiv.innerHTML = `
            <div class="ai-message-content">
                <div class="ai-message-avatar assistant"><img src="{{ asset('modules/agenticai/images/Chatbot Chat Message.jpg') }}" alt="Workpilot AI" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;"></div>
                <div class="ai-message-body">
                    <div class="ai-typing">
                        <div class="ai-typing-dot"></div>
                        <div class="ai-typing-dot"></div>
                        <div class="ai-typing-dot"></div>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(typingDiv);
        this.scrollToBottom();
    },

    removeTypingIndicator() {
        const indicator = document.getElementById('aiTypingIndicator');
        if (indicator) indicator.remove();
    },

    async createNewConversation() {
        this.currentConversationId = null;
        this.messages = [];
        this.showEmptyState();
        this.renderConversations();
        window.history.pushState({}, '', window.location.pathname);
        document.getElementById('aiInput').focus();
    },

    async sendMessage() {
        const input = document.getElementById('aiInput');
        const content = input.value.trim();
        // Allow sending if there is content OR an attachment
        if ((!content && !this.currentAttachment) || this.isLoading) return;

        this.isLoading = true;
        input.value = '';
        input.style.height = 'auto';
        document.getElementById('aiSendBtn').disabled = true;

        // Capture attachment reference before clearing (but clear UI)
        const attachment = this.currentAttachment;
        this.clearAttachment();

        try {
            // If no conversation, create one first
            if (!this.currentConversationId) {
                // For initial message, we might need to handle attachment too, 
                // but usually initial message is just text. Let's assume text for new conv for now
                // or update create logic if needed. 
                // Ideally we create conversation effectively. 
                // Simplification for now: Only text starts conversation usually.
                
                const createResponse = await fetch('/api/ai/conversations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
                    },
                    body: JSON.stringify({ 
                        initial_message: content || "Sent an attachment",
                        voice_language: document.getElementById('voiceLanguage') ? document.getElementById('voiceLanguage').value : 'en-US'
                    })
                });

                if (!createResponse.ok) {
                    const errorText = await createResponse.text();
                    console.error('Create conversation failed:', createResponse.status, errorText);
                    throw new Error(`Server returned ${createResponse.status}: ${errorText}`);
                }

                const createData = await createResponse.json();
                if (createData.status === 'success') {
                    this.currentConversationId = createData.conversation.id;
                    this.hideEmptyState();
                    
                    // Display user message
                    this.appendMessageToDOM(createData.message);
                    
                    // Sanket v2.0 - store() now returns immediately (no AI call inside)
                    // Start streaming the AI response now — tokens appear in ~1s
                    this.createPendingAIMessage();
                    
                    const aiResponse = await this.sendMessageToConversation(createData.message.content, attachment);
                    this.removeTypingIndicator();
                    this.hideInlineStatus();
                    if (aiResponse) {
                        this.morphPendingMessage(aiResponse);
                    } else {
                        this.resetPendingState();
                    }
                    
                    await this.loadConversations();
                }
            } else {
                // Add user message to UI
                const userMsg = {
                    id: Date.now(),
                    sender: 'user',
                    content: content,
                    created_at: new Date().toISOString(),
                    metadata: attachment ? { attachment_url: attachment.url } : null
                };
                this.appendMessageToDOM(userMsg);
                
                // === COPILOT-STYLE UX ===
                this.createPendingAIMessage();
                this.showTypingIndicator();
                
                const statusTimer = setTimeout(() => {
                    this.showInlineStatus('Checking…');
                }, 500);

                const aiResponse = await this.sendMessageToConversation(content || "Sent an attachment", attachment);
                clearTimeout(statusTimer);
                
                this.removeTypingIndicator();
                this.hideInlineStatus();
                
                if (aiResponse) {
                    this.morphPendingMessage(aiResponse);
                } else {
                    this.resetPendingState();
                }
            }
        } catch (error) {
            console.error('Failed to send message:', error);
            this.removeTypingIndicator();
        } finally {
            this.isLoading = false;
        }
    },

    async sendMessageToConversation(content, attachment = null) {
        // Sanket v2.0 - use streaming endpoint so tokens appear immediately (~1s) instead of waiting 10-15s
        // Falls back to regular endpoint if streaming is not supported
        const csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

        // Create the pending AI bubble that will be filled token by token
        const pendingDiv = document.getElementById(this.pendingMessageId);
        const textDiv = pendingDiv ? pendingDiv.querySelector('.ai-message-text') : null;
        let streamedText = '';

        const response = await fetch(`/api/ai/conversations/${this.currentConversationId}/stream`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'text/event-stream',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ content }),
        });

        if (!response.ok || !response.body) {
            // Fallback to regular endpoint if streaming not available
            const fallback = await fetch(`/api/ai/conversations/${this.currentConversationId}/messages`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ content }),
            });
            const data = await fallback.json();
            if (data.status === 'success') {
                const aiMessage = data.messages.find(m => m.sender === 'assistant');
                return aiMessage;
            }
            throw new Error(data.message || 'Failed to get response');
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';
        let messageId = null;

        // Sanket v2.0 - once first token arrives, clear the "Let me check that for you..." placeholder
        let firstToken = true;

        this.hideInlineStatus();

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n');
            buffer = lines.pop(); // keep incomplete last line

            for (const line of lines) {
                if (!line.startsWith('data: ')) continue;
                let parsed;
                try { parsed = JSON.parse(line.slice(6)); } catch { continue; }

                if (parsed.error) throw new Error(parsed.error);

                if (parsed.token !== undefined) {
                    streamedText += parsed.token;
                    if (textDiv) {
                        if (firstToken) {
                            // Replace placeholder text immediately when first token arrives
                            textDiv.innerHTML = '';
                            firstToken = false;
                        }
                        // Render incrementally — use innerHTML for markdown-like display
                        textDiv.innerHTML = this.formatMessage(streamedText);
                        this.scrollToBottom();
                    }
                }

                if (parsed.done) {
                    messageId = parsed.message_id;
                }
            }
        }

        // Return a synthetic message object so morphPendingMessage gets the final content
        return { id: messageId, sender: 'assistant', content: streamedText, created_at: new Date().toISOString() };
    },

    sendQuickMessage(message) {
        document.getElementById('aiInput').value = message;
        document.getElementById('aiSendBtn').disabled = false;
        this.sendMessage();
    },

    async deleteConversation(id, event) {
        event.stopPropagation();
        if (!confirm('Permanently delete this conversation?')) return;

        try {
            await fetch(`/api/ai/conversations/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
                }
            });

            if (this.currentConversationId === id) {
                this.createNewConversation();
            }
            await this.loadConversations();
            this.showToast('Conversation deleted');
        } catch (error) {
            console.error('Failed to delete conversation:', error);
            this.showToast('Delete failed', 'error');
        }
    },

    startRename(id, event) {
        if (event) event.stopPropagation();
        this.editingId = id;
        this.renderConversations();
    },

    cancelRename() {
        this.editingId = null;
        this.renderConversations();
    },

    async saveRename(id) {
        const input = document.getElementById(`rename-input-${id}`);
        if (!input) return;
        
        const newTitle = input.value.trim();
        if (!newTitle) {
            this.cancelRename();
            return;
        }

        try {
            const response = await fetch(`/api/ai/conversations/${id}`, {
                method: 'PATCH', // Controller handles this
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
                },
                body: JSON.stringify({ title: newTitle })
            });

            if (response.ok) {
                this.editingId = null;
                await this.loadConversations();
                this.showToast('Title updated');
            }
        } catch (error) {
            console.error('Rename failed:', error);
            this.showToast('Rename failed', 'error');
        }
    },

    async toggleArchive(id, event) {
        if (event) event.stopPropagation();
        const action = this.isArchiveView ? 'unarchive' : 'archive';
        
        try {
            const response = await fetch(`/api/ai/conversations/${id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
                },
                body: JSON.stringify({ is_archived: !this.isArchiveView })
            });

            if (response.ok) {
                if (this.currentConversationId === id) {
                    this.createNewConversation();
                }
                await this.loadConversations();
                this.showToast(`Conversation ${action}d`);
            }
        } catch (error) {
            console.error('Archive failed:', error);
            this.showToast('Action failed', 'error');
        }
    },

    copyMessage(id) {
        // Implementation for copy functionality
        console.log('Copy message:', id);
    },

    handleKeyPress(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            this.sendMessage();
        }
    },

    autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 200) + 'px';
    },

    scrollToBottom() {
        const container = document.getElementById('aiMessages');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    },

    // === COPILOT-STYLE UX SYSTEM ===
    
    // UX Translation Layer: Internal states → User-friendly text
    UX_TRANSLATION: {
        'understanding': 'Checking…',
        'planning': null, // No UI update
        'executing': 'Fetching details…',
        'formatting': null, // No UI update
        'completed': null // Morph message
    },
    
    pendingMessageId: null,
    statusUpdateCount: 0,
    
    // Show inline status (max 2 updates per request)
    showInlineStatus(message) {
        if (this.statusUpdateCount >= 2) return; // Hard limit
        
        const statusEl = document.getElementById('aiInlineStatus');
        const messageEl = document.getElementById('statusMessage');
        
        if (statusEl && messageEl && message) {
            messageEl.textContent = message;
            statusEl.style.display = 'block';
            this.statusUpdateCount++;
        }
    },
    
    // Hide inline status instantly (no animation)
    hideInlineStatus() {
        const statusEl = document.getElementById('aiInlineStatus');
        if (statusEl) {
            statusEl.style.display = 'none';
        }
        this.statusUpdateCount = 0;
    },
    
    // Parse internal state markers and update UI if needed
    parseAndShowAgentState(content) {
        // Simple implementation to avoid crash
        // This could extract the latest state if needed, but for history we might not want to show it
        // unless it's the very last message and clearly incomplete.
        // For now, no-op is safer than crashing.
    },

    // Centralized message cleaner
    cleanMessageContent(content) {
        if (!content) return '';
        
        let cleanContent = content;
        
        // Remove emoji state markers with their lines
        cleanContent = cleanContent.replace(/🔍\s*UNDERSTANDING:?[^\n]*/gi, '');
        cleanContent = cleanContent.replace(/⚙️\s*EXECUTING:?[^\n]*/gi, '');
        cleanContent = cleanContent.replace(/📋\s*PLANNING:?[^\n]*/gi, '');
        cleanContent = cleanContent.replace(/⏳\s*WAITING:?[^\n]*/gi, '');
        cleanContent = cleanContent.replace(/✅\s*COMPLETED:?[^\n]*/gi, '');
        cleanContent = cleanContent.replace(/❌\s*FAILED:?[^\n]*/gi, '');
        
        // Remove step markers
        cleanContent = cleanContent.replace(/\[Step\s+\d+\/\d+\][^\n]*/gi, '');
        
        // Remove [Next] and [Action] markers with their content
        cleanContent = cleanContent.replace(/\[Next\]\s*[^\n]*/gi, '');
        cleanContent = cleanContent.replace(/\[Action\]\s*[^\n]*/gi, '');
        
        // Remove any standalone state words at start of lines
        cleanContent = cleanContent.replace(/^\s*(UNDERSTANDING|PLANNING|EXECUTING|WAITING|COMPLETED|FAILED):?[^\n]*/gim, '');
        
        // Clean up excessive newlines (more than 2 consecutive)
        cleanContent = cleanContent.replace(/\n{3,}/g, '\n\n');
        
        return cleanContent.trim();
    },
    
    // Create immediate AI bubble with placeholder
    createPendingAIMessage() {
        const container = document.getElementById('aiMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'ai-message-row ai-message-pending';
        
        const pendingId = 'pending-' + Date.now();
        messageDiv.id = pendingId;
        this.pendingMessageId = pendingId;
        const avatar = `<div class="ai-message-avatar assistant"><img src="{{ asset('modules/agenticai/images/Chatbot Chat Message.jpg') }}" alt="Workpilot AI" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;"></div>`;
        const time = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        
        messageDiv.innerHTML = `
            <div class="ai-message-content">
                ${avatar}
                <div class="ai-message-body">
                    <div class="ai-message-header">
                        <span class="ai-message-sender">Workpilot AI</span>
                        <span class="ai-message-time">${time}</span>
                    </div>
                    <div class="ai-message-text">Let me check that for you…</div>
                </div>
            </div>
        `;
        
        container.appendChild(messageDiv);
        this.scrollToBottom();
        
        return pendingId;
    },
    
    // Morph pending message into final response
    morphPendingMessage(message) {
        if (!this.pendingMessageId) return;
        
        const pendingDiv = document.getElementById(this.pendingMessageId);
        if (!pendingDiv) return;
        
        // Sanket v2.0 - if content was already streamed token-by-token, textDiv already has final content
        // Only overwrite if textDiv still shows the placeholder (non-streaming fallback path)
        const textDiv = pendingDiv.querySelector('.ai-message-text');
        const isPlaceholder = textDiv && textDiv.textContent.includes('Let me check that for you');
        if (isPlaceholder && message.content) {
            const cleanContent = this.cleanMessageContent(message.content);
            textDiv.innerHTML = this.formatMessage(cleanContent);
        }
        
        // Remove pending class
        pendingDiv.classList.remove('ai-message-pending');
        
        // Add copy button
        const bodyDiv = pendingDiv.querySelector('.ai-message-body');
        if (bodyDiv) {
            const actionsHTML = `
                <div class="ai-message-actions">
                    <button class="ai-message-action-btn" onclick="aiApp.copyMessage('${message.id}')">
                        <i class="far fa-copy"></i> Copy
                    </button>
                </div>
            `;
            bodyDiv.insertAdjacentHTML('beforeend', actionsHTML);
        }
        
        this.pendingMessageId = null;
        this.scrollToBottom();

        // Voice Mode: Speak the response
        if (this.isVoiceActive) {
            this.speakResponse(cleanContent);
        }
    },
    
    // Reset pending state
    resetPendingState() {
        this.pendingMessageId = null;
        this.statusUpdateCount = 0;
        this.hideInlineStatus();
    },

    hideEmptyState() {
        const emptyState = document.getElementById('aiEmptyState');
        if (emptyState) emptyState.style.display = 'none';
    },

    showEmptyState() {
        const container = document.getElementById('aiMessages');
        container.innerHTML = '';
        const emptyState = document.getElementById('aiEmptyState');
        if (emptyState) {
            container.appendChild(emptyState);
            emptyState.style.display = 'flex';
        }
    },

    toggleHistory() {
        document.getElementById('aiHistoryPanel').classList.toggle('open');
    },
    
    toggleSidebar() {
        const panel = document.getElementById('aiHistoryPanel');
        panel.classList.toggle('collapsed');
        
        // Toggle icon rotation
        const btn = document.querySelector('.ai-header-toggle-btn');
        if (btn) btn.classList.toggle('collapsed');
    },

    showToast(message) {
        const toast = document.getElementById('aiToast');
        if (toast) {
            toast.textContent = message;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
    }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    aiApp.init();
});
</script>
@endsection
