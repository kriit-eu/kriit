<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --primary-blue: #3b82f6;
        --primary-blue-light: #dbeafe;
        --primary-blue-dark: #1e40af;
        --success-green: #10b981;
        --success-green-light: #d1fae5;
        --warning-orange: #f59e0b;
        --warning-orange-light: #fef3c7;
        --purple: #8b5cf6;
        --purple-light: #ede9fe;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        --border-radius: 12px;
        --border-radius-lg: 16px;
        --border-radius-sm: 8px;
    }

    body {
        background-color: var(--gray-50);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        line-height: 1.6;
        color: var(--gray-700);
    }

    .red-cell {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%) !important;
        border-color: #f87171 !important;
        color: #b91c1c !important;
    }

    .yellow-cell {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%) !important;
        border-color: #f59e0b !important;
        color: #92400e !important;
    }

    .modal-body {
        word-wrap: break-word;
        word-break: break-word;
    }

    #solutionUrl {
        display: inline-block;
        max-width: 100%;
        overflow-wrap: break-word;
        word-break: break-all;
    }

    .modal-dialog {
        max-width: 900px;
        width: 100%;
    }

    .modal-content {
        padding: 20px;
        border: none;
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-xl);
    }

    .modal-header {
        border-bottom: 1px solid var(--gray-200);
        padding-bottom: 1rem;
    }

    .modal-title {
        font-weight: 600;
        color: var(--gray-900);
    }

    .text-center {
        text-align: center;
    }

    .context-menu {
        display: none;
        position: absolute;
        z-index: 1000;
        width: 280px;
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-lg);
        padding: 16px;
        overflow-x: auto;
    }

    .context-menu .grades,
    .context-menu .criteria {
        display: inline-block;
        vertical-align: top;
    }

    .context-menu .grades {
        width: 45%;
    }

    .context-menu .criteria {
        width: 45%;
        max-width: 100%;
    }

    .form-check-label {
        word-wrap: break-word;
        word-break: break-all;
        font-weight: 500;
        color: var(--gray-700);
    }

    .context-menu ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .context-menu ul li {
        padding: 10px 12px;
        cursor: pointer;
        border-radius: var(--border-radius-sm);
        transition: all 0.2s ease;
        font-weight: 500;
    }

    .context-menu ul li:hover {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue-dark);
    }

    .context-menu .form-check {
        margin: 8px 2px 8px 0 !important;
    }

    .context-menu .form-check label {
        margin-right: 10px;
    }

    .student-criteria-section h5 {
        margin-bottom: 12px;
        color: var(--gray-800);
        font-weight: 600;
    }

    .form-check {
        margin-bottom: 8px;
    }

    .form-check-input:checked {
        background-color: var(--primary-blue);
        border-color: var(--primary-blue);
    }

    .form-check-input:focus {
        box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        border-color: var(--primary-blue);
    }

    /* Enhanced Button Styles */
    .btn {
        font-weight: 500;
        border-radius: var(--border-radius-sm);
        padding: 0.5rem 1rem;
        transition: all 0.2s ease;
        border: none;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
        color: white;
        box-shadow: var(--shadow-sm);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--primary-blue-dark) 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .btn-secondary {
        background: var(--gray-100);
        color: var(--gray-700);
        border: 1px solid var(--gray-300);
    }

    .btn-secondary:hover {
        background: var(--gray-200);
        border-color: var(--gray-400);
        transform: translateY(-1px);
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success-green) 0%, #059669 100%);
        color: white;
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-1px);
    }

    /* Image preview styles for comments */
    .comment-image {
        transition: all 0.3s ease;
        border-radius: var(--border-radius-sm);
    }

    .comment-image:hover {
        transform: scale(1.02);
        box-shadow: var(--shadow-lg);
    }

    .image-preview-container {
        text-align: left;
    }

    .image-modal-content {
        max-width: 90vw;
        max-height: 90vh;
        object-fit: contain;
        border-radius: var(--border-radius);
    }

    /* Modal backdrop for image viewing */
    .image-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        backdrop-filter: blur(4px);
    }

    .image-modal-backdrop img {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-xl);
    }

    .image-modal-close {
        position: absolute;
        top: 30px;
        right: 40px;
        color: white;
        font-size: 32px;
        font-weight: bold;
        cursor: pointer;
        z-index: 10000;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .image-modal-close:hover {
        background: rgba(0, 0, 0, 0.7);
        transform: scale(1.1);
    }

    .criteria-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid var(--gray-100);
    }

    .criteria-row:last-child {
        border-bottom: none;
    }

    .criteria-row .form-check {
        flex-grow: 1;
    }

    .criteria-row button {
        margin-left: 12px;
    }

    .clickable-cells-row {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .clickable-cells-row:hover {
        background-color: var(--gray-50);
    }

    .assignments-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid var(--gray-200);
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        background: white;
    }

    .header-item,
    .body-item {
        text-align: center;
        padding: 16px;
        border: 1px solid var(--gray-200);
        min-height: 60px;
        vertical-align: middle;
        white-space: nowrap;
        font-weight: 500;
    }

    .header-item {
        background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-200) 100%);
        font-weight: 600;
        color: var(--gray-800);
    }

    .comment-cell {
        padding: 12px;
        border: 1px solid var(--gray-200);
        vertical-align: top;
        max-width: 200px;
    }

    .comments-container {
        max-height: 320px;
        overflow-y: auto;
    }

    .comment-item {
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: var(--border-radius-sm);
        padding: 12px;
        margin-bottom: 10px;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .comment-item:hover {
        background: white;
        box-shadow: var(--shadow-sm);
    }

    .comment-item:last-child {
        margin-bottom: 0;
    }

    .comment-name {
        font-weight: 600;
        color: var(--gray-800);
        margin-bottom: 6px;
    }

    .comment-text {
        color: var(--gray-600);
        margin-bottom: 6px;
        word-wrap: break-word;
        line-height: 1.4;
    }

    .comment-date {
        font-size: 0.75rem;
        color: var(--gray-400);
    }

    .adaptive-background {
        width: 100%;
        max-width: 600px;
        min-width: 300px;
        padding: 20px;
        margin: 0;
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-sm);
    }

    #notificationContainer {
        max-height: 500px;
        border: 2px solid var(--primary-blue);
        background: linear-gradient(135deg, var(--primary-blue-light) 0%, #f0f9ff 100%);
        box-shadow: var(--shadow-md);
        border-radius: var(--border-radius);
        padding: 8px;
        margin-bottom: 24px;
    }

    #notificationContainer .content-part {
        max-height: 400px;
        overflow-y: auto;
        padding: 8px;
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
    }

    .notification-item {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        margin-bottom: 12px;
        padding: 12px;
        border-radius: var(--border-radius-sm);
        background: white;
        box-shadow: var(--shadow-sm);
        transition: all 0.2s ease;
        border: 1px solid var(--gray-100);
    }

    .notification-item:hover {
        background: var(--gray-50);
        box-shadow: var(--shadow-md);
        transform: translateY(-1px);
    }

    .notification-icon {
        margin-right: 8px;
        color: var(--primary-blue);
        font-size: 20px;
        flex-shrink: 0;
    }

    .notification-text {
        flex-grow: 1;
        font-size: 0.875rem;
        color: var(--gray-700);
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
        white-space: normal;
        min-width: 0;
        font-weight: 500;
    }

    .notification-text p {
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
        white-space: normal;
        margin-bottom: 0;
    }

    .notification-time {
        font-size: 0.75rem;
        color: var(--gray-400);
        margin-left: 8px;
        flex-shrink: 0;
        font-weight: 500;
    }

    #messageContainer {
        max-height: 600px;
        overflow-x: hidden;
        border: 1px solid var(--gray-200);
        border-radius: var(--border-radius);
        background: white;
    }

    /* Enhanced typography for message container */
    #messageContainer,
    #messageContainer * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        font-weight: normal !important;
        font-style: normal !important;
        font-variant: normal !important;
        text-rendering: optimizeLegibility !important;
        -webkit-font-smoothing: antialiased !important;
        -moz-osx-font-smoothing: grayscale !important;
    }

    #messageContainer .fw-bold {
        font-weight: 600 !important;
    }

    #messageContainer h6 {
        font-size: 1rem !important;
        font-weight: 600 !important;
        margin-bottom: 0.5rem !important;
        color: var(--gray-800) !important;
    }

    #messageContainer p {
        font-size: 0.875rem !important;
        line-height: 1.6 !important;
        margin-bottom: 1rem !important;
        color: var(--gray-700) !important;
    }

    #messageContainer small {
        font-size: 0.75rem !important;
        color: var(--gray-500) !important;
    }

    #messageContainer .btn {
        font-size: 0.75rem !important;
        font-weight: 500 !important;
    }

    .info-icon {
        color: var(--gray-500) !important;
        font-size: 1rem !important;
        margin-right: 0.5rem !important;
        font-weight: normal !important;
        display: inline-block !important;
    }

    .reply-button {
        display: inline-block !important;
        padding: 4px 10px !important;
        font-size: 0.75rem !important;
        font-weight: 500 !important;
        color: var(--gray-600) !important;
        background: var(--gray-100) !important;
        border: 1px solid var(--gray-300) !important;
        border-radius: var(--border-radius-sm) !important;
        cursor: pointer !important;
        text-decoration: none !important;
        transition: all 0.2s ease !important;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
    }

    .reply-button:hover {
        color: var(--gray-800) !important;
        background: var(--gray-200) !important;
        border-color: var(--gray-400) !important;
        transform: translateY(-1px) !important;
    }

    .reply-button:active {
        transform: translateY(0) !important;
    }

    .content-part {
        max-height: 300px;
        overflow-y: auto;
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
        white-space: normal;
    }

    #messageContainer .content-part {
        max-height: 500px;
    }

    .card-body {
        word-wrap: break-word;
    }

    /* Enhanced main layout */
    #assignments-container {
        flex: 1;
        max-width: 100%;
        overflow-x: auto;
        border-radius: var(--border-radius);
    }

    #messages-container {
        flex: 2;
        display: flex;
        flex-direction: column;
        gap: 2rem;
        margin-top: 2rem;
    }

    @media (min-width: 769px) {
        .adaptive-background {
            margin-left: 0;
        }

        #messageContainer {
            max-width: 100%;
        }
    }

    .pre-wrap {
        white-space: pre-wrap;
        text-align: left;
    }

    .comment-row {
        border: 1px solid var(--gray-200);
        padding: 8px;
        margin-bottom: 8px;
        border-radius: var(--border-radius-sm);
        background: var(--gray-50);
        transition: all 0.2s ease;
    }

    .comment-row:hover {
        background: white;
        box-shadow: var(--shadow-sm);
    }

    /* Message content image styles */
    .content-part img {
        display: block;
        margin: 12px 0;
        max-width: 100%;
        height: auto;
        border-radius: var(--border-radius-sm);
        box-shadow: var(--shadow-sm);
        clear: both;
        border: 1px solid var(--gray-200);
    }

    .content-part p {
        line-height: 1.6;
        word-wrap: break-word;
    }

    .content-part p img {
        margin: 16px 0;
    }

    /* Enhanced blockquote styling */
    .content-part blockquote {
        background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
        border-left: 4px solid var(--primary-blue);
        margin: 12px 0;
        padding: 16px 20px;
        border-radius: var(--border-radius-sm);
        font-style: italic;
        color: var(--gray-600);
        box-shadow: var(--shadow-sm);
    }

    .content-part blockquote p {
        margin-bottom: 6px;
        line-height: 1.5;
    }

    .content-part blockquote p:last-child {
        margin-bottom: 0;
    }

    .content-part blockquote strong {
        color: var(--gray-700);
        font-weight: 600;
    }

    .content-part blockquote em {
        color: var(--gray-500);
        font-size: 0.9em;
    }

    .message-content {
        font-family: 'JetBrains Mono', 'Monaco', 'Menlo', 'Ubuntu Mono', monospace !important;
    }

    /* Enhanced message item styling */
    .message-item {
        background: white !important;
        border: 1px solid var(--gray-200) !important;
        border-radius: var(--border-radius) !important;
        margin-bottom: 1.5rem !important;
        transition: all 0.2s ease !important;
        box-shadow: var(--shadow-sm) !important;
        min-height: 80px !important;
    }

    .message-item:hover {
        background: var(--gray-50) !important;
        border-color: var(--primary-blue) !important;
        box-shadow: var(--shadow-md) !important;
        transform: translateY(-1px) !important;
    }

    .notification-item {
        background: linear-gradient(135deg, var(--warning-orange-light) 0%, #fef3c7 100%) !important;
        border-color: var(--warning-orange) !important;
    }

    .notification-item:hover {
        background: linear-gradient(135deg, #fde68a 0%, #f59e0b 10%) !important;
        border-color: #d97706 !important;
    }

    .message-item .avatar {
        width: 44px !important;
        height: 44px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1rem !important;
        font-weight: 600 !important;
        flex-shrink: 0 !important;
        border-radius: 50% !important;
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%) !important;
        color: white !important;
    }

    .message-item .flex-grow-1 {
        min-width: 0 !important;
    }

    /* Enhanced Unified Communication Bar Styles */
    .unified-communication-bar {
        display: flex;
        flex-direction: column;
        gap: 2rem;
        margin-top: 2rem;
    }

    .communication-section {
        width: 100%;
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-md);
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .communication-section:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }

    .section-header {
        padding: 1.25rem;
        border-bottom: 1px solid var(--gray-200);
        background: linear-gradient(135deg, var(--gray-50) 0%, white 100%);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    /* Enhanced section themes */
    #commentsSection .section-header {
        background: linear-gradient(135deg, var(--primary-blue-light) 0%, #f0f9ff 100%);
        border-bottom-color: var(--primary-blue);
    }

    #commentsSection .section-header h5 {
        color: var(--primary-blue-dark);
    }

    #activitiesSection .section-header {
        background: linear-gradient(135deg, var(--warning-orange-light) 0%, #fef3c7 100%);
        border-bottom-color: var(--warning-orange);
    }

    #activitiesSection .section-header h5 {
        color: #d97706;
    }

    #criteriaSection .section-header {
        background: linear-gradient(135deg, var(--purple-light) 0%, #f3e8ff 100%);
        border-bottom-color: var(--purple);
    }

    #criteriaSection .section-header h5 {
        color: #7c3aed;
    }

    #chatSection .section-header {
        background: linear-gradient(135deg, var(--success-green-light) 0%, #ecfdf5 100%);
        border-bottom-color: var(--success-green);
    }

    #chatSection .section-header h5 {
        color: #047857;
    }

    .section-header h5 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-800);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-shrink: 0;
    }

    .section-content {
        padding: 1.25rem;
        max-height: 600px;
        overflow-y: auto;
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
    }
    
    /* Increase height for chat section specifically */
    #chatSection .section-content {
        max-height: 800px;
    }
    
    /* Increase height for activities section */
    #activitiesSection .section-content {
        max-height: 750px;
    }
    
    /* Increase height for comments section */
    #commentsSection .section-content {
        max-height: 700px;
    }

    /* Custom scrollbar */
    .section-content::-webkit-scrollbar {
        width: 6px;
    }

    .section-content::-webkit-scrollbar-track {
        background: var(--gray-100);
        border-radius: 3px;
    }

    .section-content::-webkit-scrollbar-thumb {
        background: var(--gray-300);
        border-radius: 3px;
    }

    .section-content::-webkit-scrollbar-thumb:hover {
        background: var(--gray-400);
    }

    .unified-item {
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: var(--border-radius);
        padding: 1rem;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }

    .unified-item:hover {
        background: white;
        border-color: var(--gray-300);
        box-shadow: var(--shadow-sm);
        transform: translateY(-1px);
    }

    .unified-item:last-child {
        margin-bottom: 0;
    }

    .item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .item-author {
        font-weight: 600;
        color: var(--gray-800);
        font-size: 0.9rem;
    }

    .item-time {
        color: var(--gray-500);
        font-size: 0.75rem;
        font-weight: 500;
    }

    .item-content {
        color: var(--gray-700);
        font-size: 0.875rem;
        line-height: 1.5;
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
        white-space: pre-wrap;
        text-align: left;
    }

    .item-meta {
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--gray-200);
        font-size: 0.75rem;
        color: var(--gray-500);
        font-style: italic;
    }

    .no-content {
        text-align: center;
        color: var(--gray-500);
        font-style: italic;
        padding: 3rem;
        font-size: 0.9rem;
    }

    /* Enhanced Message Form Integration Styles */
    .section-footer {
        border-top: 1px solid var(--gray-200);
        background: linear-gradient(135deg, var(--gray-50) 0%, white 100%);
        padding: 1.25rem;
    }

    .message-form {
        margin: 0;
    }

    .message-input {
        border-radius: var(--border-radius-sm) 0 0 var(--border-radius-sm) !important;
        border-right: none !important;
        resize: none;
        font-size: 0.9rem;
        border-color: var(--gray-300) !important;
        transition: all 0.2s ease;
    }

    .message-input:focus {
        border-color: var(--primary-blue) !important;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25) !important;
    }

    .message-submit {
        border-radius: 0 var(--border-radius-sm) var(--border-radius-sm) 0 !important;
        padding: 0.5rem 1.25rem;
        font-size: 0.9rem;
        white-space: nowrap;
        font-weight: 500;
    }

    .reply-info {
        margin-bottom: 1rem;
        padding: 1rem;
        background: linear-gradient(135deg, var(--primary-blue-light) 0%, #f0f9ff 100%);
        border: 1px solid var(--primary-blue);
        border-radius: var(--border-radius);
        font-size: 0.875rem;
    }

    .reply-preview {
        flex-grow: 1;
        color: var(--primary-blue-dark);
        font-style: italic;
        font-weight: 500;
    }

    .btn-close-reply {
        background: none;
        border: none;
        font-size: 1.25rem;
        color: var(--gray-500);
        cursor: pointer;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
    }

    .btn-close-reply:hover {
        background: rgba(0,0,0,0.1);
        color: var(--gray-700);
        transform: scale(1.1);
    }

    /* Enhanced Student Summary Integration Styles */
    .students-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--gray-200);
    }

    /* Current student header badge for student view */
    .current-student-header-badge {
        display: flex;
        align-items: center;
        flex-shrink: 0;
    }

    .current-student-header-badge .student-badge {
        margin: 0;
        border: 2px solid var(--primary-blue);
        box-shadow: var(--shadow-md);
        transform: scale(1.1);
    }

    .current-student-header-badge .student-badge:hover {
        transform: scale(1.15);
        box-shadow: var(--shadow-lg);
    }

    .student-badge {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-width: 70px;
        padding: 0.75rem;
        border: 2px solid var(--gray-200);
        border-radius: var(--border-radius);
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.8rem;
        position: relative;
        overflow: hidden;
    }

    .student-badge::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.1) 100%);
        pointer-events: none;
    }

    .student-badge:hover {
        background: var(--gray-50);
        border-color: var(--primary-blue);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .student-badge.red-cell {
        background: linear-gradient(135deg, #fee2e2 0%, #fca5a5 100%) !important;
        border-color: #f87171 !important;
        color: #b91c1c !important;
    }

    .student-badge.yellow-cell {
        background: linear-gradient(135deg, var(--warning-orange-light) 0%, #fde68a 100%) !important;
        border-color: var(--warning-orange) !important;
        color: #92400e !important;
    }

    .student-initials {
        font-weight: 700;
        color: var(--gray-800);
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }

    .student-grade {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--primary-blue);
    }

    .student-criteria {
        font-size: 0.7rem;
        color: var(--gray-500);
        margin-top: 0.25rem;
        font-weight: 500;
    }

    /* Enhanced criteria specific styling */
    .criteria-item {
        border: none !important;
        background: none !important;
        padding: 0.75rem 0 !important;
        margin-bottom: 0.5rem !important;
        border-bottom: 1px solid var(--gray-100) !important;
    }

    .criteria-item:last-child {
        border-bottom: none !important;
    }

    .criteria-item:hover {
        background: var(--gray-50) !important;
        border-radius: var(--border-radius-sm) !important;
        box-shadow: none !important;
    }

    .criteria-item .form-check {
        margin: 0;
    }

    .criteria-item .form-check-label {
        font-size: 0.9rem;
        line-height: 1.5;
        color: var(--gray-700);
        cursor: pointer;
        padding-left: 0.5rem;
        font-weight: 500;
    }

    .criteria-item .form-check-input {
        margin-top: 0.25rem;
        width: 1.1em;
        height: 1.1em;
    }

    /* Enhanced section content scrolling */
    #commentsSection .section-content {
        max-height: 450px;
        overflow-y: auto;
        scroll-behavior: smooth;
    }

    #criteriaSection .section-content {
        max-height: 350px;
        overflow-y: auto;
    }

    /* Enhanced responsive adjustments */
    @media (max-width: 768px) {
        .section-content {
            max-height: 300px;
            padding: 1rem;
        }
        
        .unified-item {
            padding: 0.75rem;
        }
        
        .item-content {
            font-size: 0.8rem;
        }

        .communication-section {
            border-radius: var(--border-radius);
        }

        .section-header {
            padding: 1rem;
        }

        .students-summary {
            gap: 0.5rem;
        }

        .student-badge {
            min-width: 60px;
            padding: 0.5rem;
        }
    }

    /* Enhanced form styling */
    .form-control {
        border: 1px solid var(--gray-300);
        border-radius: var(--border-radius-sm);
        padding: 0.75rem;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        outline: none;
    }

    .form-label {
        font-weight: 600;
        color: var(--gray-800);
        margin-bottom: 0.5rem;
    }

    /* Enhanced loading states */
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }

    /* Enhanced hover effects for interactive elements */
    .clickable-cells-row:hover td {
        background-color: var(--gray-50) !important;
    }

    /* Animation classes */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .fade-in {
        animation: fadeIn 0.3s ease;
    }

    /* Current User Highlighting */
    .current-user-content {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%) !important;
        border-left: 4px solid var(--primary-blue) !important;
        border-color: var(--primary-blue) !important;
        position: relative;
    }

    .current-user-content::before {
        content: '👤';
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        font-size: 0.75rem;
        opacity: 0.8;
        color: var(--primary-blue);
    }

    .current-user-content .item-author {
        color: var(--primary-blue-dark) !important;
        font-weight: 700 !important;
    }

    .current-user-content:hover {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%) !important;
        border-left-color: var(--primary-blue-dark) !important;
    }

    /* Modal Comments Section Styling */
    #commentsContainer {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid var(--gray-200);
        border-radius: var(--border-radius);
        background: var(--gray-50);
        padding: 0.5rem;
        margin-top: 0.5rem;
    }

    #commentsContainer:empty::before {
        content: "Kommentaare pole.";
        display: block;
        text-align: center;
        color: var(--gray-500);
        font-style: italic;
        padding: 2rem;
    }

    #commentsContainer .unified-item {
        margin-bottom: 0.75rem;
        background: white;
        border: 1px solid var(--gray-200);
        padding: 0.75rem;
    }

    #commentsContainer .unified-item:last-child {
        margin-bottom: 0;
    }

    #commentsContainer .item-header {
        margin-bottom: 0.5rem;
    }

    #commentsContainer .item-content {
        font-size: 0.875rem;
        line-height: 1.5;
    }

    /* Modal comments scrollbar */
    #commentsContainer::-webkit-scrollbar {
        width: 6px;
    }

    #commentsContainer::-webkit-scrollbar-track {
        background: var(--gray-100);
        border-radius: 3px;
    }

    #commentsContainer::-webkit-scrollbar-thumb {
        background: var(--gray-300);
        border-radius: 3px;
    }

    #commentsContainer::-webkit-scrollbar-thumb:hover {
        background: var(--gray-400);
    }
</style>
<div>
    <div class="mb-3">
        <h2 class="mb-2"><?= $assignment['assignmentName'] ?></h2>
        <?php if (!empty($assignment['primaryGroupName'])): ?>
            <p class="text-muted mb-3">
                <i class="bi bi-people-fill"></i> Grupp: <strong><?= $assignment['primaryGroupName'] ?></strong>
            </p>
        <?php endif; ?>
        <?php
        $parsedown = new Parsedown();
        $assignmentInstructions = $assignment['assignmentInstructions'];

        // Check if the text contains Markdown syntax
        if (strpos($assignmentInstructions, '#') !== false || strpos($assignmentInstructions, '*') !== false || strpos($assignmentInstructions, '-') !== false) {
            $assignmentInstructionsHtml = $parsedown->text($assignmentInstructions);
        } else {
            $assignmentInstructionsHtml = nl2br(htmlspecialchars($assignmentInstructions));
        }
        ?>
        <p class="mb-0"><?= $assignmentInstructionsHtml ?></p>
        <p class="mt-4 fw-bold">Tähtaeg: <?= $assignment['assignmentDueAt'] ?></p>

        <?php if ($isStudent): ?>
            <div class="d-flex justify-content-end mt-3">
                <span <?php if ($assignment['students'][$this->auth->userId]['isDisabledStudentActionButton'] === 'disabled'): ?>
                    data-bs-toggle="tooltip" title="
                            <?php if ($assignment['students'][$this->auth->userId]['isAllCriteriaCompleted']): ?>
                            Ülesanne on juba hinnatud
                            <?php else: ?>
                            Kõik kriteeriumid pole sul veel märgitud valmis
                            <?php endif; ?>"
                    <?php endif; ?>>
                    <button class="btn btn-primary"
                        onclick="openStudentModal(true, <?= $this->auth->userId ?>)" <?= $assignment['students'][$this->auth->userId]['isDisabledStudentActionButton'] ?>>
                        <?= $assignment['students'][$this->auth->userId]['studentActionButtonName'] ?>
                    </button>
                </span>
            </div>
        <?php else: ?>
            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-secondary" onclick="editAssignment()">Muuda</button>
            </div>
        <?php endif; ?>

    </div>




    <?php if (!$isStudent): ?>
        <div class="modal fade " id="editAssignmentModal" tabindex="-1" aria-labelledby="editAssignmentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAssignmentModalLabel">Muuta ülesanne</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editAssignmentForm">
                            <div class="mb-3">
                                <label for="assignmentName" class="form-label">Pealkiri</label>
                                <input type="text" class="form-control" id="assignmentName" name="assignmentName"
                                    value="<?= $assignment['assignmentName'] ?>">
                            </div>
                            <div class="mb-3">
                                <label for="assignmentInstructions" class="form-label">Instruktsioon</label>
                                <textarea class="form-control" id="assignmentInstructions" name="assignmentInstructions"
                                    rows="3"><?= $assignment['assignmentInstructions'] ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="assignmentDueAt" class="form-label">Tähtaeg</label>
                                <input type="date" class="form-control" id="assignmentDueAt" name="assignmentDueAt"
                                    value="<?= (!empty($assignment['assignmentDueAt']) && strtotime($assignment['assignmentDueAt']) > 0) ? date('Y-m-d', strtotime($assignment['assignmentDueAt'])) : "" ?>">
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="assignmentInvolvesOpenApi" name="assignmentInvolvesOpenApi"
                                    <?= isset($assignment['assignmentInvolvesOpenApi']) && $assignment['assignmentInvolvesOpenApi'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="assignmentInvolvesOpenApi">Ülesandel on OpenAPI</label>
                            </div>


                            <!-- Block for criteria management -->
                            <div class="mb-3">
                                <h5>Kriteeriumid</h5>
                                <div id="editCriteriaContainer">
                                    <?php foreach ($assignment['criteria'] as $criterion): ?>
                                        <div class="criteria-row">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    id="edit_criterion_<?= $criterion['criteriaId'] ?>" checked
                                                    disabled>
                                                <label class="form-check-label"
                                                    for="edit_criterion_<?= $criterion['criteriaId'] ?>">
                                                    <?= htmlspecialchars($criterion['criteriaName'], ENT_QUOTES, 'UTF-8') ?>
                                                </label>
                                            </div>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="removeOldCriterion(<?= $criterion['criteriaId'] ?>)">X
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="btn btn-primary mt-2" id="addCriterionButton">Lisa
                                    kriteerium
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="saveEditedAssignment()">Salvesta</button>
                        <button type="button" class="btn btn-secondary" onclick="location.reload()"
                            data-bs-dismiss="modal">Tühista
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="addCriterionModal" tabindex="-1" aria-labelledby="addCriterionModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCriterionModalLabel">Lisa uus kriteerium</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addCriterionForm">
                            <div class="mb-3">
                                <label for="newCriterionName" class="form-label">Kriteeriumi nimi</label>
                                <textarea class="form-control" id="newCriterionName" name="newCriterionName" rows="3"
                                    placeholder="Sisestage kriteeriumi nimi"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="addNewCriterion()">Lisa</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tühista</button>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

    <div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentName"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="solutionUrl" class="form-label fw-bold">Lahenduse link</label>
                        <div id="solutionInputContainer">
                            <input type="text" id="solutionInput" class="form-control"
                                placeholder="Sisesta link siia...">
                            <small id="solutionInputFeedback"></small>
                        </div>

                        <p class="mt-1" id="solutionUrlContainer">
                            <a href="#" id="solutionUrl" target="_blank" rel="noopener noreferrer">No link provided</a>
                        </p>
                    </div>
                    <?php include 'views/modules/openapi_module.php'; ?>
                    <?php if (!$isStudent): ?>
                        <div id="gradeSection" class="mb-3" style="display: none;">
                            <label class="form-label fw-bold">Hinne</label>
                            <div id="gradeRadioGroup" class="d-flex justify-content-around">
                                <div class="text-center">
                                    <input class="form-check-input" type="radio" name="grade" id="grade5" value="5">
                                    <label class="form-check-label d-block" for="grade5">5</label>
                                </div>
                                <div class="text-center">
                                    <input class="form-check-input" type="radio" name="grade" id="grade4" value="4">
                                    <label class="form-check-label d-block" for="grade4">4</label>
                                </div>
                                <div class="text-center">
                                    <input class="form-check-input" type="radio" name="grade" id="grade3" value="3">
                                    <label class="form-check-label d-block" for="grade3">3</label>
                                </div>
                                <div class="text-center">
                                    <input class="form-check-input" type="radio" name="grade" id="grade2" value="2">
                                    <label class="form-check-label d-block" for="grade2">2</label>
                                </div>
                                <div class="text-center">
                                    <input class="form-check-input" type="radio" name="grade" id="grade1" value="1">
                                    <label class="form-check-label d-block" for="grade1">1</label>
                                </div>
                                <div class="text-center">
                                    <input class="form-check-input" type="radio" name="grade" id="gradeA" value="A">
                                    <label class="form-check-label d-block" for="gradeA">A</label>
                                </div>
                                <div class="text-center">
                                    <input class="form-check-input" type="radio" name="grade" id="gradeMA" value="MA">
                                    <label class="form-check-label d-block" for="gradeMA">MA</label>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div id="studentGradeCriteriaContainer">
                        <h6 class="fw-bold">Kriteeriumid</h6>
                        <div id="checkboxesContainer">
                        </div>
                    </div>
                    <div id="commentSection" class="mb-3">
                        <label for="studentComment" class="form-label fw-bold">Kommentaar</label>
                        <div id="commentsContainer"></div>
                        <textarea class="form-control" id="studentComment" rows="3"
                            placeholder="Lisa kommentaar siia..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="submitButton">
                        Salvesta muudatused
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tühista</button>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$isStudent): ?>
        <div id="context-menu" class="context-menu">
            <div class="grades">
                <ul>
                    <li onclick="setGrade(5)">5</li>
                    <li onclick="setGrade(4)">4</li>
                    <li onclick="setGrade(3)">3</li>
                    <li onclick="setGrade(2)">2</li>
                    <li onclick="setGrade(1)">1</li>
                    <li onclick="setGrade('A')">A</li>
                    <li onclick="setGrade('MA')">MA</li>
                </ul>
            </div>
            <div class="criteria">
            </div>
        </div>
    <?php endif; ?>

    <div id="main-container">
        <div id="messages-container">
            <div class="unified-communication-bar">
                <!-- Criteria Section -->
                <div class="communication-section" id="criteriaSection">
                    <div class="section-header">
                        <h5>✓ Kriteeriumid</h5>
                    </div>
                    <div class="section-content" id="criterionDisplay">
                        <form id="studentCriteriaForm">
                            <div id="requiredCriteria">
                                <?php foreach ($assignment['criteria'] as $criterion): ?>
                                    <?php
                                    $isCompleted = true;
                                    $studentId = $this->auth->userId;

                                    if ($isStudent && isset($assignment['students'][$studentId]['userDoneCriteria'][$criterion['criteriaId']])) {
                                        $isCompleted = $assignment['students'][$studentId]['userDoneCriteria'][$criterion['criteriaId']]['completed'];
                                    }
                                    ?>
                                    <div class="unified-item criteria-item">
                                        <div class="form-check">
                                            <input class="form-check-input" id="criterion_<?= $criterion['criteriaId'] ?>"
                                                type="checkbox"
                                                name="criteria[<?= $criterion['criteriaId'] ?>]"
                                                value="1" <?= $isCompleted ? 'checked' : '' ?>
                                                <?= $isStudent ? '' : 'disabled' ?>>
                                            <label class="form-check-label" for="criterion_<?= $criterion['criteriaId'] ?>">
                                                <?= htmlspecialchars($criterion['criteriaName'], ENT_QUOTES, 'UTF-8') ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($isStudent): ?>
                                <div class="section-footer">
                                    <button type="button" class="btn btn-success btn-sm" onclick="saveStudentCriteria()" hidden="hidden">
                                        Salvesta kriteeriumid
                                    </button>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="communication-section" id="commentsSection">
                    <div class="section-header">
                        <h5>💬 Kommentaarid</h5>
                        <?php if ($isStudent): ?>
                            <!-- Show current student's badge in header corner for student view -->
                            <?php $currentStudent = $assignment['students'][$this->auth->userId]; ?>
                            <div class="current-student-header-badge">
                                <div class="student-badge <?= $currentStudent['class'] ?>" 
                                     data-bs-toggle="tooltip" 
                                     title="<?= $currentStudent['studentName'] ?>: <?= $currentStudent['tooltipText'] ?>"
                                     onclick="openStudentModal(true, <?= $currentStudent['studentId'] ?>)">
                                    <span class="student-initials"><?= $currentStudent['initials'] ?></span>
                                    <span class="student-grade"><?= $currentStudent['grade'] ?? '—' ?></span>
                                    <?php if ($currentStudent['assignmentStatusName'] !== 'Esitamata'): ?>
                                        <span class="student-criteria"><?= $currentStudent['userDoneCriteriaCount'] ?>/<?= count($assignment['criteria']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Show all students for teacher/admin view -->
                            <div class="students-summary">
                                <?php foreach ($assignment['students'] as $s): ?>
                                    <div class="student-badge <?= $s['class'] ?>" 
                                         data-bs-toggle="tooltip" 
                                         title="<?= $s['studentName'] ?>: <?= $s['tooltipText'] ?>"
                                         onclick="openStudentModal(false, <?= $s['studentId'] ?>)">
                                        <span class="student-initials"><?= $s['initials'] ?></span>
                                        <span class="student-grade"><?= $s['grade'] ?? '—' ?></span>
                                        <?php if ($s['assignmentStatusName'] !== 'Esitamata'): ?>
                                            <span class="student-criteria"><?= $s['userDoneCriteriaCount'] ?>/<?= count($assignment['criteria']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="section-content">
                        <?php
                        $allComments = [];
                        foreach ($assignment['students'] as $student) {
                            if (!empty($student['comments'])) {
                                foreach ($student['comments'] as $comment) {
                                    $comment['studentName'] = $student['studentName'];
                                    $comment['studentUserId'] = $student['studentId']; // Add student user ID for comparison
                                    $allComments[] = $comment;
                                }
                            }
                        }
                        // Sort comments by date (newest first)
                        usort($allComments, function($a, $b) {
                            return strtotime($b['createdAt']) - strtotime($a['createdAt']);
                        });
                        ?>
                        <?php foreach ($allComments as $comment): ?>
                            <?php 
                            $isCurrentUser = false;
                            $debugInfo = '';
                            
                            // Check if this comment is from the current user
                            // Comments structure: { name: "Name", comment: "text", createdAt: "date" }
                            // They don't have userId or userEmail, only name matching
                            if (isset($comment['name']) && isset($this->auth->userName) && 
                                !empty($comment['name']) && !empty($this->auth->userName) && 
                                trim($comment['name']) === trim($this->auth->userName)) {
                                $isCurrentUser = true;
                                $debugInfo = 'matched by name: ' . $comment['name'] . ' == ' . $this->auth->userName;
                            }
                            
                            // Additional debug info to see in browser console via data attribute
                            $debugData = json_encode([
                                'isCurrentUser' => $isCurrentUser,
                                'matchedBy' => $debugInfo,
                                'commentData' => [
                                    'name' => $comment['name'] ?? 'none',
                                    'comment' => substr($comment['comment'] ?? '', 0, 50) . '...',
                                    'createdAt' => $comment['createdAt'] ?? 'none'
                                ],
                                'authData' => [
                                    'userName' => $this->auth->userName ?? 'none',
                                    'userId' => $this->auth->userId
                                ]
                            ]);
                            ?>
                            <div class="unified-item<?= $isCurrentUser ? ' current-user-content' : '' ?>" data-debug="<?= htmlspecialchars($debugData, ENT_QUOTES, 'UTF-8') ?>">
                                <div class="item-header">
                                    <span class="item-author"><?= isset($comment['name']) ? $comment['name'] : 'Tundmatu' ?></span>
                                    <small class="item-time"><?= $comment['createdAt'] ?></small>
                                </div>
                                <div class="item-content comment-text" data-raw-comment="<?= htmlspecialchars($comment['comment'], ENT_QUOTES, 'UTF-8') ?>">
                                    <!-- Comment content will be processed by JavaScript -->
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($allComments)): ?>
                            <div class="no-content">Kommentaare pole</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Activities/Notifications Section -->
                <div class="communication-section" id="activitiesSection">
                    <div class="section-header">
                        <h5>📋 Sündmused</h5>
                    </div>
                    <div class="section-content">
                        <?php 
                        // Filter notifications (already sorted newest first from database)
                        $notifications = array_filter($assignment['messages'], function($message) {
                            return $message['isNotification'];
                        });
                        // No need to sort again - database query already returns newest first
                        ?>
                        <?php foreach ($notifications as $message): ?>
                            <?php 
                            $isCurrentUser = $message['userId'] == $this->auth->userId;
                            ?>
                            <div class="unified-item<?= $isCurrentUser ? ' current-user-content' : '' ?>">
                                <div class="item-header">
                                    <span class="item-author"><?= $message['userName'] ?></span>
                                    <small class="item-time"><?= $message['createdAt'] ?></small>
                                </div>
                                <div class="item-content">
                                    <?= strip_tags($message['content'], '<br><ul><ol><h2><li><h3><p><strong><img><blockquote><em><code><pre>') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($notifications)): ?>
                            <div class="no-content">Sündmusi pole</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Chat/Vestlus Section -->
                <div class="communication-section" id="chatSection">
                    <div class="section-header">
                        <h5>💭 Vestlus</h5>
                    </div>
                    <div class="section-content">
                        <?php 
                        // Filter chat messages (already sorted newest first from database)
                        $chatMessages = array_filter($assignment['messages'], function($message) {
                            return !$message['isNotification'];
                        });
                        // No need to sort again - database query already returns newest first
                        ?>
                        <?php foreach ($chatMessages as $message): ?>
                            <?php 
                            $isCurrentUser = $message['userId'] == $this->auth->userId;
                            ?>
                            <div class="unified-item<?= $isCurrentUser ? ' current-user-content' : '' ?>">
                                <div class="item-header">
                                    <span class="item-author"><?= $message['userName'] ?></span>
                                    <small class="item-time"><?= $message['createdAt'] ?></small>
                                    <?php if (!$isCurrentUser): ?>
                                        <span class="reply-button" 
                                              onclick='replyToMessage(<?= json_encode($message['userName']) ?>, <?= $message['messageId'] ?>, <?= json_encode($message['userEmail'] ?? 'No email') ?>, "<?= $message['createdAt'] ?>")'>
                                            Vasta
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="item-content">
                                    <?= nl2br(htmlspecialchars(strip_tags($message['content']), ENT_QUOTES, 'UTF-8')) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($chatMessages)): ?>
                            <div class="no-content">Sõnumeid pole</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Message Input Form -->
                    <div class="section-footer">
                        <div id="replyInfo" class="reply-info" style="display:none;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div id="replyMessage" class="reply-preview"></div>
                                <button type="button" class="btn-close-reply" onclick="cancelReply()">×</button>
                            </div>
                        </div>
                        <form class="message-form">
                            <div class="input-group">
                                <textarea class="form-control message-input" id="messageContent" name="content" rows="2"
                                    placeholder="Kirjuta oma sõnum siia..."></textarea>
                                <button type="button" class="btn btn-primary message-submit" onclick="submitMessage()">Postita</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>




        </div>


    </div>
    <script>
        const assignment = <?= json_encode($assignment) ?>;
        let currentStudentId = null;
        let newAddedCriteria = [];
        const userIsAdmin = <?= $this->auth->userIsAdmin ? 'true' : 'false' ?>;

        document.addEventListener('DOMContentLoaded', function() {
            initializeTooltips();
            processComments(); // Process comments first
            
            // Debug: Log current user highlighting info and message counts
            debugCurrentUserHighlighting();
            debugMessageCounts();
            
            // Delay scrolling to allow content to render properly
            setTimeout(function() {
                scrollToBottom();
            }, 100);

            // Initialize OpenAPI button visibility
            const openApiButton = document.getElementById('openApiButton');
            if (openApiButton) {
                openApiButton.style.display = assignment.assignmentInvolvesOpenApi ? 'inline-block' : 'none';
            }
        });

        function debugCurrentUserHighlighting() {
            const commentsWithDebug = document.querySelectorAll('[data-debug]');
            console.log('Debug info for user highlighting:');
            commentsWithDebug.forEach((item, index) => {
                try {
                    const debugData = JSON.parse(item.getAttribute('data-debug'));
                    console.log(`Comment ${index + 1}:`, debugData);
                    if (debugData.isCurrentUser) {
                        console.log(`✓ Comment ${index + 1} should be highlighted (${debugData.matchedBy})`);
                    }
                } catch (e) {
                    console.log(`Error parsing debug data for comment ${index + 1}:`, e);
                }
            });
        }

        function debugMessageCounts() {
            const commentsCount = document.querySelectorAll('#commentsSection .unified-item').length;
            const activitiesCount = document.querySelectorAll('#activitiesSection .unified-item').length;
            const chatCount = document.querySelectorAll('#chatSection .unified-item').length;
            
            console.log('Message counts:');
            console.log(`Comments: ${commentsCount}`);
            console.log(`Activities: ${activitiesCount}`);
            console.log(`Chat: ${chatCount}`);
            console.log(`Total assignment messages from server: ${assignment.messages ? assignment.messages.length : 0}`);
            
            if (assignment.messages) {
                const notifications = assignment.messages.filter(m => m.isNotification);
                const chats = assignment.messages.filter(m => !m.isNotification);
                console.log(`Server - Notifications: ${notifications.length}, Chat messages: ${chats.length}`);
                
                // Show the actual chat messages and their timestamps
                console.log('Chat messages details:');
                chats.forEach((msg, index) => {
                    console.log(`${index + 1}. ${msg.userName} (${msg.createdAt}${msg.createdAtRaw ? ` / raw: ${msg.createdAtRaw}` : ''}): ${msg.content.substring(0, 50)}...`);
                });
            }
        }

        function debugCheckboxes() {
            console.log('🔍 Debugging checkbox elements:');
            
            // Main page checkboxes
            const mainCheckboxes = document.querySelectorAll('#requiredCriteria input[type="checkbox"]');
            console.log(`Main page checkboxes found: ${mainCheckboxes.length}`);
            mainCheckboxes.forEach((cb, index) => {
                console.log(`  ${index + 1}. ID: ${cb.id}, checked: ${cb.checked}`);
            });
            
            // Modal checkboxes
            const modalCheckboxes = document.querySelectorAll('#checkboxesContainer input[type="checkbox"]');
            console.log(`Modal checkboxes found: ${modalCheckboxes.length}`);
            modalCheckboxes.forEach((cb, index) => {
                console.log(`  ${index + 1}. ID: ${cb.id}, checked: ${cb.checked}, data-criteria-id: ${cb.dataset.criteriaId}`);
            });
        }

        function initializeTooltips() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // If no criteria exists, display warning (add class 'warning-bg' to element of id 'criterionDisplay')

        if (Array.isArray(assignment['criteria']) && assignment['criteria'].length === 0 || assignment['criteria'] === null) {
            document.getElementById('criterionDisplay').classList.add('bg-warning');
            document.getElementById('requiredCriteria').innerHTML = '<p class="text-center">Kriteeriumid puuduvad</p>';
        }

        document.getElementById('requiredCriteria').addEventListener('change', function(event) {
            if (event.target && event.target.type === 'checkbox') {
                const saveButton = document.querySelector('#studentCriteriaForm .btn-primary');
                if (saveButton) {
                    saveButton.hidden = false;
                }
                
                // Sync with modal checkboxes if modal is open
                if (event.target.id && event.target.id.startsWith('criterion_')) {
                    const criteriaId = event.target.id.replace('criterion_', '');
                    if (criteriaId) {
                        console.log(`Main page checkbox changed: criterion_${criteriaId} = ${event.target.checked}`);
                        syncCriteriaCheckboxes(criteriaId, event.target.checked);
                    }
                }
            }
        });

        document.getElementById('submitButton').addEventListener('click', function() {
            const gradeRadioGroup = document.querySelectorAll('#gradeRadioGroup input[type="radio"]');
            let gradeSelected = false;

            gradeRadioGroup.forEach(radio => {
                if (radio.checked) {
                    gradeSelected = true;
                }
            });

            <?php if (!$isStudent): ?>
                if (!gradeSelected) {
                    alert('Palun vali hinne.');
                    return;
                }
            <?php endif ?>

            if (submitButton.textContent === 'Esita' || submitButton.textContent === 'Muuda') {
                const solutionUrl = solutionInput.value;
                const criteria = getCriteriaList();
                const comment = document.getElementById('studentComment').value;

                ajax(`assignments/saveStudentSolutionUrl`, {
                        assignmentId: assignment.assignmentId,
                        studentId: <?= $this->auth->userId ?>,
                        studentName: assignment.students[<?= $this->auth->userId ?>].studentName,
                        solutionUrl: solutionUrl,
                        criteria: criteria,
                        teacherId: assignment.teacherId,
                        teacherName: assignment.teacherName,
                        comment: comment
                    },
                    function(res) {
                        if (res.status === 200) {
                            location.reload();
                        }
                    },
                    function(error) {
                        alert(error ?? 'Tekkis viga serveriga suhtlemisel.');
                    });

            } else {
                const grade = document.querySelector('#gradeSection input[type="radio"]:checked')?.value;
                const criteria = getCriteriaList();

                const comment = document.getElementById('studentComment').value;

                ajax(`assignments/saveAssignmentGrade`, {
                        assignmentId: assignment.assignmentId,
                        studentId: currentStudentId,
                        grade: grade,
                        criteria: criteria,
                        comment: comment,
                        teacherId: assignment.teacherId,
                        teacherName: assignment.teacherName,
                        studentName: assignment.students[currentStudentId].studentName
                    },
                    function(res) {
                        if (res.status === 200) {
                            location.reload();
                        }
                    },
                    function(error) {
                        alert(error ?? 'Tekkis viga serveriga suhtlemisel.');
                    });
            }
        });


        document.querySelectorAll('#studentGradeCriteriaContainer input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const allChecked = Array.from(document.querySelectorAll('#studentGradeCriteriaContainer input[type="checkbox"]'))
                    .every(cb => cb.checked);
                document.getElementById('submitButton').disabled = !allChecked;
            });
        });

        function showContextMenu(event, studentId) {
            event.preventDefault();
            currentStudentId = studentId;

            const menu = document.getElementById('context-menu');
            const criteriaContainer = menu.querySelector('.criteria');

            criteriaContainer.innerHTML = '';

            const student = assignment.students[studentId];
            const allCriteria = assignment.criteria;

            if (student && allCriteria) {
                Object.keys(assignment.criteria).forEach(criteriaId => {
                    const criterion = assignment.criteria[criteriaId];
                    const isCompleted = assignment.students[studentId]?.userDoneCriteria[criteriaId]?.completed;

                    criteriaContainer.innerHTML += `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="check_criterion_${criteriaId}" ${isCompleted ? 'checked' : ''}>
                    <label class="form-check-label" for="check_criterion_${criteriaId}">
                        ${criterion.criteriaName}
                    </label>
                </div>
            `;
                });
            }

            menu.style.display = 'block';
            menu.style.left = `${event.pageX}px`;
            menu.style.top = `${event.pageY}px`;

            adjustDropdownPosition(menu, event.pageX, event.pageY);

            document.addEventListener('click', hideContextMenu);
        }

        function adjustDropdownPosition(menu, pageX, pageY) {
            const menuRect = menu.getBoundingClientRect();
            const windowWidth = window.innerWidth;
            const windowHeight = window.innerHeight;

            if (menuRect.right > windowWidth) {
                menu.style.left = `${pageX - menuRect.width}px`;
            }

            if (menuRect.bottom > windowHeight) {
                menu.style.top = `${pageY - menuRect.height}px`;
            }
        }

        function hideContextMenu() {
            const menu = document.getElementById('context-menu');
            menu.style.display = 'none';
            document.removeEventListener('click', hideContextMenu);
        }

        function openStudentModal(isStudent, studentId = null) {
            const modalTitle = document.getElementById('studentName');
            const solutionInputContainer = document.getElementById('solutionInputContainer');
            const solutionInput = document.getElementById('solutionInput');
            const solutionUrlContainer = document.getElementById('solutionUrlContainer');
            const submitButton = document.getElementById('submitButton');
            const criteriaContainer = document.getElementById('checkboxesContainer');
            const commentsContainer = document.getElementById('commentsContainer'); // Container for comments
            const student = assignment.students[studentId];
            currentStudentId = studentId;

            // Reset comments container to ensure it's cleared before populating with new comments
            commentsContainer.innerHTML = '';

            // Populate comments section with unified styling
            if (student && student.comments && student.comments.length > 0) {
                // Sort comments by date (newest first)
                const sortedComments = student.comments.slice().sort((a, b) => {
                    return new Date(b.createdAt) - new Date(a.createdAt);
                });

                sortedComments.forEach(comment => {
                    const commentItem = document.createElement('div');
                    commentItem.classList.add('unified-item');

                    // Check if this is the current user's comment
                    const currentUserId = <?= $this->auth->userId ?>;
                    const currentUserName = "<?= $this->auth->userName ?? '' ?>";
                    
                    let isCurrentUser = false;
                    // Only match by name since comments only have name field
                    if (comment.name && currentUserName && 
                        comment.name.trim() === currentUserName.trim()) {
                        isCurrentUser = true;
                    }
                    
                    if (isCurrentUser) {
                        commentItem.classList.add('current-user-content');
                    }

                    // Create item header
                    const itemHeader = document.createElement('div');
                    itemHeader.classList.add('item-header');
                    
                    const authorSpan = document.createElement('span');
                    authorSpan.classList.add('item-author');
                    authorSpan.textContent = comment.name || 'Tundmatu';
                    
                    const timeSpan = document.createElement('small');
                    timeSpan.classList.add('item-time');
                    timeSpan.textContent = comment.createdAt;
                    
                    itemHeader.appendChild(authorSpan);
                    itemHeader.appendChild(timeSpan);

                    // Create item content
                    const itemContent = document.createElement('div');
                    itemContent.classList.add('item-content', 'comment-text');
                    itemContent.setAttribute('data-raw-comment', comment.comment);
                    
                    // Append elements
                    commentItem.appendChild(itemHeader);
                    commentItem.appendChild(itemContent);
                    commentsContainer.appendChild(commentItem);
                });
                
                // Process the comments with markdown parsing
                processModalComments();
                
                // Scroll to top to show the newest comment first
                setTimeout(() => {
                    commentsContainer.scrollTop = 0;
                }, 50);
            } else {
                const noComments = document.createElement('div');
                noComments.classList.add('no-content');
                noComments.textContent = 'Kommentaare pole.';
                commentsContainer.appendChild(noComments);
            }

            if (isStudent) {
                modalTitle.textContent = 'Sisesta Lahendus';
                solutionInputContainer.style.display = 'block'; // Show the input for students to enter a link
                submitButton.textContent = student.studentActionButtonName;
                submitButton.disabled = true; // Initially disable the "Esita" button

                // Add trimming functionality to solution input
                solutionInput.addEventListener('input', function() {
                    // Trim the value and update the input
                    const trimmedValue = this.value.trim();
                    if (this.value !== trimmedValue) {
                        this.value = trimmedValue;
                    }
                    updateSubmitButtonState();
                });

                // Also handle paste events to trim pasted content
                solutionInput.addEventListener('paste', function(e) {
                    setTimeout(() => {
                        const trimmedValue = this.value.trim();
                        if (this.value !== trimmedValue) {
                            this.value = trimmedValue;
                        }
                        updateSubmitButtonState();
                    }, 0);
                });

                document.getElementById('checkboxesContainer').addEventListener('change', function(event) {
                    if (event.target && event.target.type === 'checkbox') {
                        updateButtonState();
                    }
                });

                solutionInput.addEventListener('input', updateSubmitButtonState);

                let isValidUrl = false;

                async function updateSubmitButtonState() {
                    const solutionUrlValue = solutionInput.value.trim();
                    const solutionInputFeedback = document.getElementById('solutionInputFeedback');

                    if (solutionUrlValue === '') {
                        solutionInputFeedback.textContent = '';
                        submitButton.disabled = true;
                        return;
                    }
                    try {
                        ajax('assignments/validateAndCheckLinkAccessibility', {
                            solutionUrl: solutionUrlValue
                        }, function(res) {
                            if (res.status === 200) {
                                solutionInputFeedback.textContent = 'Link on valideeritud ja kättesaadav.';
                                solutionInputFeedback.style.color = 'green';
                                isValidUrl = true;
                                updateButtonState();
                            }
                        }, function(error) {
                            solutionInputFeedback.textContent = error || 'Link on vigane või kättesaamatu.';
                            solutionInputFeedback.style.color = 'red';
                            isValidUrl = false;
                            updateButtonState();
                        });
                    } catch (error) {
                        solutionInputFeedback.textContent = 'Tekkis viga URL-i valideerimisel';
                        solutionInputFeedback.style.color = 'red';
                        isValidUrl = false;
                        updateButtonState();
                    }

                    updateButtonState();
                }

                function updateButtonState() {
                    const allChecked = Array.from(document.querySelectorAll('#checkboxesContainer input[type="checkbox"]'))
                        .every(cb => cb.checked);

                    submitButton.disabled = !(allChecked && isValidUrl);
                }

            } else {
                const gradeSection = document.getElementById('gradeSection');
                const commentSection = document.getElementById('commentSection');
                const openApiButton = document.getElementById('openApiButton');

                modalTitle.textContent = student.studentName;
                gradeSection.style.display = 'block';
                commentSection.style.display = 'block';
                solutionInputContainer.style.display = 'none';
                submitButton.textContent = 'Salvesta';
                submitButton.disabled = false;

                // Show/hide the OpenAPI button based on the assignment's assignmentInvolvesOpenApi value
                if (openApiButton) {
                    openApiButton.style.display = assignment.assignmentInvolvesOpenApi ? 'inline-block' : 'none';
                }

                if (student.grade) {
                    document.querySelector(`#gradeRadioGroup input[value="${student.grade}"]`).checked = true;
                } else {
                    document.querySelectorAll('#gradeRadioGroup input[type="radio"]').forEach(rb => {
                        rb.checked = false;
                    });
                }

                if (student.comment) {
                    document.getElementById('studentComment').value = student.comment;
                } else {
                    document.getElementById('studentComment').value = '';
                }
            }

            if (student.solutionUrl) {
                solutionUrlContainer.innerHTML = `
            <?php if ($isStudent): ?>
                <p class="pt-2 mb-0">Juba esitatud lahendus:</p>
            <?php endif ?>
            <a href="${student.solutionUrl}" id="solutionUrl" target="_blank" rel="noopener noreferrer">${student.solutionUrl}</a>`;
            } else {
                solutionUrlContainer.innerHTML = 'Link puudub'; // Display plain text if no link
            }

            criteriaContainer.innerHTML = '';

            Object.keys(assignment.criteria).forEach((criteriaId, index) => {
                const criterion = assignment.criteria[criteriaId];
                
                // Check current state of main page checkbox first, fallback to server data
                const mainPageCheckbox = document.querySelector(`#requiredCriteria #criterion_${criteriaId}`);
                let isCompleted;
                
                if (mainPageCheckbox) {
                    // Use current state from main page
                    isCompleted = mainPageCheckbox.checked;
                    console.log(`Using main page state for criterion ${criteriaId}: ${isCompleted}`);
                } else {
                    // Fallback to server data
                    isCompleted = assignment.students[studentId]?.userDoneCriteria[criteriaId]?.completed;
                    console.log(`Using server state for criterion ${criteriaId}: ${isCompleted}`);
                }

                criteriaContainer.innerHTML += `
    <div class="form-check">
        <input class="form-check-input modal-criterion-checkbox" type="checkbox" id="criterion_${criteriaId}" data-criteria-id="${criteriaId}" ${isCompleted ? 'checked' : ''}>
        <label class="form-check-label" for="criterion_${criteriaId}">
            ${index + 1}. ${criterion.criteriaName}
        </label>
    </div>
    `;
            });

            // Add event listeners to sync checkboxes
            const modalCheckboxes = criteriaContainer.querySelectorAll('.modal-criterion-checkbox');
            modalCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    console.log(`Modal checkbox changed: criterion_${this.dataset.criteriaId} = ${this.checked}`);
                    syncCriteriaCheckboxes(this.dataset.criteriaId, this.checked);
                });
            });


            const modal = new bootstrap.Modal(document.getElementById('studentModal'));
            modal.show();
            
            // Debug checkboxes after modal is shown
            setTimeout(() => {
                debugCheckboxes();
            }, 100);
        }

        function saveStudentCriteria() {
            const criteria = getCriteriaList('#requiredCriteria input[type="checkbox"]');
            ajax(`assignments/saveStudentCriteria`, {
                    assignmentId: assignment.assignmentId,
                    studentId: <?= $this->auth->userId ?>,
                    criteria: criteria,
                    teacherId: assignment.teacherId,
                    teacherName: assignment.teacherName
                },
                function(res) {
                    if (res.status === 200) {
                        location.reload();
                    }
                },
                function(error) {
                    alert(error ?? 'Tekkis viga serveriga suhtlemisel.');
                });
        }

        function getCriteriaList(selector = '#studentGradeCriteriaContainer input[type="checkbox"]') {
            const criteria = {};
            document.querySelectorAll(selector).forEach(cb => {
                if (selector.startsWith('#edit')) {
                    criteria[parseInt(cb.id.replace('edit_criterion_', ''))] = cb.checked;
                } else if (selector.startsWith('#context-menu') || selector.startsWith('#check')) {
                    criteria[parseInt(cb.id.replace('check_criterion_', ''))] = cb.checked;
                } else {
                    criteria[parseInt(cb.id.replace('criterion_', ''))] = cb.checked;
                }
            });
            return criteria;
        }

        function scrollToBottom() {
            const chatSection = document.querySelector('#chatSection .section-content');
            const activitiesSection = document.querySelector('#activitiesSection .section-content');
            const commentsSection = document.querySelector('#commentsSection .section-content');

            // Comments and activities scroll to top (newest items first)
            if (commentsSection) {
                commentsSection.scrollTop = 0;
            }
            
            if (activitiesSection) {
                activitiesSection.scrollTop = 0;
            }

            // Chat section scrolls to bottom (traditional chat behavior - but since we sort newest first, actually scroll to top)
            if (chatSection) {
                chatSection.scrollTop = 0;
            }
        }


        function editAssignment() {
            const modal = new bootstrap.Modal(document.getElementById('editAssignmentModal'));
            modal.show();
        }

        function removeOldCriterion(param) {
            const editCriteriaContainer = document.getElementById('editCriteriaContainer');
            const criterionElement = document.getElementById(`edit_criterion_${param}`);

            if (criterionElement) {
                const criterionRow = criterionElement.closest('.criteria-row');
                if (criterionRow) {
                    editCriteriaContainer.removeChild(criterionRow);
                } else {
                    console.error("Criterion row not found");
                }
            } else {
                console.error("Criterion element not found");
            }
        }

        function saveEditedAssignment() {
            const assignmentName = document.getElementById('assignmentName').value;
            const assignmentInstructions = document.getElementById('assignmentInstructions').value;
            const assignmentDueAt = document.getElementById('assignmentDueAt').value;
            const assignmentInvolvesOpenApi = document.getElementById('assignmentInvolvesOpenApi').checked ? 1 : 0;
            const criteria = getCriteriaList('#editCriteriaContainer input[type="checkbox"]');
            ajax(`assignments/editAssignment`, {
                    assignmentId: assignment.assignmentId,
                    teacherId: assignment.teacherId,
                    teacherName: assignment.teacherName,
                    assignmentName: assignmentName,
                    assignmentInstructions: assignmentInstructions,
                    assignmentDueAt: assignmentDueAt,
                    assignmentInvolvesOpenApi: assignmentInvolvesOpenApi,
                    oldCriteria: criteria,
                    newCriteria: newAddedCriteria ?? [],
                },
                function(res) {
                    if (res.status === 200) {
                        location.reload();
                        scrollToBottom();
                    }
                },
                function(error) {
                    alert(error ?? 'Tekkis viga serveriga suhtlemisel.');
                }
            );
        }

        <?php if (!$isStudent): ?>
            document.getElementById('addCriterionButton').addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('addCriterionModal'));
                modal.show();
            });
        <?php endif; ?>

        function addNewCriterion() {
            const criterionName = document.getElementById('newCriterionName').value.trim();

            if (!criterionName) {
                alert('Sisestage kriteeriumi nimi!');
                return;
            }

            ajax('assignments/checkCriterionNameSize', {
                criterionName: criterionName
            }, function(res) {
                if (res.status === 200) {
                    const existingCriteria = Array.from(document.querySelectorAll('#editCriteriaContainer .form-check-label'))
                        .map(label => label.textContent.trim());

                    if (existingCriteria.includes(criterionName) || newAddedCriteria.includes(criterionName)) {
                        alert('Selline kriteerium on juba olemas!');
                        return;
                    }

                    const modal = bootstrap.Modal.getInstance(document.getElementById('addCriterionModal'));
                    modal.hide();

                    newAddedCriteria.push(criterionName);

                    const editCriteriaContainer = document.getElementById('editCriteriaContainer');

                    const criterionHTML = `
                <div class="criteria-row">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" checked disabled>
                        <label class="form-check-label">${criterionName}</label>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeNewCriterion('${criterionName}')">X</button>
                </div>
            `;
                    document.getElementById('newCriterionName').value = '';
                    editCriteriaContainer.insertAdjacentHTML('beforeend', criterionHTML);
                }
            }, function(error) {
                alert(error ?? 'Tekkis viga serveriga suhtlemisel.');
            });
        }

        function removeNewCriterion(criterionName) {
            newAddedCriteria = newAddedCriteria.filter(name => name !== criterionName);

            const criterionRow = Array.from(document.querySelectorAll('.criteria-row')).find(row => row.textContent.includes(criterionName));
            if (criterionRow) {
                criterionRow.remove();
            }
        }

        function replyToMessage(userName, messageId, userEmail, createdAt) {
            document.getElementById('replyInfo').style.display = 'block';
            document.getElementById('replyMessage').innerHTML = `
        <div class="d-flex text-break align-items-center border rounded p-3" style="background-color: #e3f2fd; border-left: 4px solid #2196f3;">
            <div class="me-3">
                <span class="avatar bg-primary text-white rounded-circle p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-size: 1rem;">${userName[0]}</span>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-primary mb-1">${userName}</div>
                <div class="text-muted small">${userEmail} • ${createdAt}</div>
            </div>
            <div class="text-muted small">
                <i class="fas fa-reply me-1"></i>Vastamiseks
            </div>
        </div>
    `;
            const content = document.getElementById('messageContent')
            content.setAttribute('data-reply-id', messageId)
            content.setAttribute('data-reply-user', userName)
            content.setAttribute('data-reply-email', userEmail)
            content.setAttribute('data-reply-time', createdAt)
            content.focus();
        }

        function cancelReply() {
            document.getElementById('replyInfo').style.display = 'none';
            document.getElementById('messageContent').removeAttribute('data-reply-id');
        }

        function submitMessage() {
            const messageContent = document.getElementById('messageContent');
            const answerToId = messageContent.getAttribute('data-reply-id') || null;

            let replyContent = '';
            if (answerToId) {
                const replyUser = messageContent.getAttribute('data-reply-user');
                const replyEmail = messageContent.getAttribute('data-reply-email');
                const replyTime = messageContent.getAttribute('data-reply-time');

                replyContent = `> **${replyUser}** (${replyEmail}) kirjutas *${replyTime}*:\n> Vastus sellele sõnumile\n\n`;
            }

            const finalContent = replyContent + messageContent.value;

            ajax('assignments/saveMessage', {
                assignmentId: assignment.assignmentId,
                userId: <?= $this->auth->userId ?>,
                content: finalContent,
                answerToId: answerToId,
                teacherId: assignment.teacherId,
                teacherName: assignment.teacherName
            }, function(res) {
                if (res.status === 200) {
                    location.reload();
                    scrollToBottom();
                }
            }, function(error) {
                alert(error ?? 'Tekkis viga serveriga suhtlemisel.');
            });
        }

        function setGrade(grade) {

            ajax(`assignments/saveAssignmentGrade`, {
                    assignmentId: assignment.assignmentId,
                    studentId: currentStudentId,
                    grade: grade,
                    teacherName: assignment.teacherName,
                    studentName: assignment.students[currentStudentId].studentName,
                    teacherId: assignment.teacherId
                },
                function(res) {
                    if (res.status === 200) {
                        location.reload();
                    }
                });
        }

        // OpenAPI functionality
        function openSwaggerModal() {
            const solutionUrl = document.getElementById('solutionUrl').getAttribute('href');
            const swaggerUrlInput = document.getElementById('swaggerUrlInput');
            const promptTextarea = document.getElementById('promptTextarea');

            // Set the default URL by appending /swagger-ui-init.js to the solution URL
            if (solutionUrl && solutionUrl !== '#') {
                let swaggerUrl = solutionUrl;

                // Handle specific SwaggerUI links like https://docs.foo.me/en/#/forms/createForm
                // Extract the base URL (everything before the #)
                if (swaggerUrl.includes('#/')) {
                    swaggerUrl = swaggerUrl.split('#/')[0];
                }

                // Make sure the URL ends with a slash before appending swagger-ui-init.js
                if (!swaggerUrl.endsWith('/')) {
                    swaggerUrl += '/';
                }
                swaggerUrl += 'swagger-ui-init.js';
                swaggerUrlInput.value = swaggerUrl;
            } else {
                swaggerUrlInput.value = '';
            }

            // Clear previous output
            document.getElementById('swaggerDocOutput').value = '';

            // Load the prompt from settings
            loadPromptFromSettings();

            // Show the modal
            const modalElement = document.getElementById('swaggerModal');
            const modal = new bootstrap.Modal(modalElement);

            // Initialize tooltips when the modal is fully shown
            modalElement.addEventListener('shown.bs.modal', function() {
                // Initialize all tooltips within the modal
                const tooltipTriggerList = [].slice.call(modalElement.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });

            modal.show();
        }

        function loadPromptFromSettings() {
            const promptTextarea = document.getElementById('promptTextarea');
            const promptDisplay = document.getElementById('promptDisplay');

            // Use AJAX to fetch the prompt from settings
            ajax('assignments/getOpenApiPrompt', {}, function(response) {
                const promptText = (response.status === 200 && response.data && response.data.prompt !== undefined) ?
                    response.data.prompt :
                    '';

                // Set the prompt text in the appropriate element
                if (userIsAdmin) {
                    // For admins: set the value of the textarea
                    promptTextarea.value = promptText;

                    // Add event listener to save the prompt when it changes
                    promptTextarea.addEventListener('input', function() {
                        // Debounce the save operation
                        if (promptTextarea.saveTimeout) {
                            clearTimeout(promptTextarea.saveTimeout);
                        }
                        promptTextarea.saveTimeout = setTimeout(function() {
                            savePromptToSettings(promptTextarea.value);
                        }, 1000); // Save after 1 second of inactivity
                    });
                } else {
                    // For non-admins: set the text content of the pre element and the hidden textarea
                    if (promptDisplay) {
                        promptDisplay.textContent = promptText;
                    }
                    promptTextarea.value = promptText; // Still set the hidden textarea for copying
                }
            }, function(error) {
                console.error('Failed to load prompt from settings:', error);
                if (userIsAdmin) {
                    promptTextarea.value = '';
                } else if (promptDisplay) {
                    promptDisplay.textContent = '';
                    promptTextarea.value = ''; // Also clear the hidden textarea
                }
            });
        }

        function savePromptToSettings(promptText) {
            // Only admins can save the prompt
            if (!userIsAdmin) return;

            ajax('assignments/saveOpenApiPrompt', {
                prompt: promptText
            }, function(response) {
                if (response.status === 200) {
                    console.log('Prompt saved successfully');
                } else {
                    console.error('Failed to save prompt:', response.error);
                }
            }, function(error) {
                console.error('Failed to save prompt:', error);
            });
        }

        function fetchSwaggerDoc() {
            const swaggerUrl = document.getElementById('swaggerUrlInput').value.trim();
            const outputTextarea = document.getElementById('swaggerDocOutput');
            const fetchButton = document.getElementById('fetchSwaggerButton');
            const copyButton = document.getElementById('copyButton');
            const loadingSpinner = document.getElementById('swaggerLoadingSpinner');

            if (!swaggerUrl) {
                showError(outputTextarea, 'Palun sisesta kehtiv URL');
                return;
            }

            // Disable the buttons while fetching
            fetchButton.disabled = true;
            copyButton.disabled = true;
            fetchButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
            outputTextarea.value = '';

            // Show the loading spinner
            loadingSpinner.classList.remove('d-none');

            // Use AJAX to fetch the swagger-ui-init.js file through a PHP proxy
            ajax('assignments/fetchSwaggerDoc', {
                url: swaggerUrl
            }, function(response) {
                // Reset UI elements
                fetchButton.disabled = false;
                fetchButton.innerHTML = 'Hangi OpenAPI spekk';
                loadingSpinner.classList.add('d-none');

                if (response.status === 200 && response.data && response.data.swaggerDoc) {
                    // Format the JSON for better readability
                    try {
                        // Get the base URL from the swagger URL
                        const swaggerDocObj = response.data.swaggerDoc;
                        const swaggerUrl = document.getElementById('swaggerUrlInput').value.trim();
                        const baseUrl = getBaseUrlFromSwaggerUrl(swaggerUrl);

                        // Check if we need to modify the servers array
                        if (baseUrl) {
                            // If servers array doesn't exist or first server is "/", add/replace with the base URL
                            if (!swaggerDocObj.servers ||
                                !swaggerDocObj.servers.length ||
                                (swaggerDocObj.servers.length > 0 && swaggerDocObj.servers[0].url === '/')) {

                                // Create servers array if it doesn't exist
                                if (!swaggerDocObj.servers) {
                                    swaggerDocObj.servers = [];
                                }

                                // Add or replace the first server with the base URL
                                if (swaggerDocObj.servers.length === 0) {
                                    swaggerDocObj.servers.push({
                                        url: baseUrl,
                                        description: 'API Server'
                                    });
                                } else {
                                    swaggerDocObj.servers[0] = {
                                        url: baseUrl,
                                        description: 'API Server'
                                    };
                                }
                            }
                        }

                        const formattedJson = JSON.stringify(swaggerDocObj, null, 2);
                        outputTextarea.value = formattedJson;
                        copyButton.disabled = false; // Enable the copy button only when we have content
                    } catch (e) {
                        showError(outputTextarea, 'Viga JSON-i vormindamisel: ' + e.message);
                        copyButton.disabled = true;
                    }
                } else {
                    let errorMessage = 'OpenAPI spetsifikatsiooni hankimine või parsimine ebaõnnestus';
                    if (response.error) {
                        errorMessage = response.error;
                    }
                    showError(outputTextarea, errorMessage);
                    copyButton.disabled = true;
                }
            }, function(error) {
                // Reset UI elements
                fetchButton.disabled = false;
                fetchButton.innerHTML = 'Hangi OpenAPI spekk';
                loadingSpinner.classList.add('d-none');

                let errorMessage = 'OpenAPI spetsifikatsiooni hankimisel tekkis viga';
                if (error) {
                    if (error.includes('404')) {
                        errorMessage = 'OpenAPI spetsifikatsiooni faili ei leitud (404). Palun kontrolli URL-i.';
                    } else if (error.includes('403')) {
                        errorMessage = 'Juurdepääs OpenAPI spetsifikatsioonile on keelatud (403). Sul ei pruugi olla õigusi sellele ressursile jõuda.';
                    } else if (error.includes('500')) {
                        errorMessage = 'Serveril tekkis viga (500) OpenAPI spetsifikatsiooni hankimisel.';
                    } else if (error.includes('timeout')) {
                        errorMessage = 'Päring aegus. Server võib olla aeglane või kättesaamatu.';
                    } else {
                        errorMessage = error;
                    }
                }

                showError(outputTextarea, errorMessage);
                copyButton.disabled = true;
            });
        }

        // Helper function to show formatted error messages
        function showError(textarea, message) {
            textarea.value = '⚠️ VIGA: ' + message;
            textarea.style.color = 'red';
            setTimeout(() => {
                textarea.style.color = ''; // Reset color after a delay
            }, 5000);
        }

        // Helper function to extract the base URL from the swagger-ui-init.js URL
        function getBaseUrlFromSwaggerUrl(swaggerUrl) {
            if (!swaggerUrl) return null;

            try {
                // Create a URL object from the swagger URL
                const urlObj = new URL(swaggerUrl);

                // Just return the origin (protocol + hostname) without any path
                // This ensures we get the root of the server (e.g., https://docs.eerovallistu.site)
                return urlObj.origin;
            } catch (e) {
                console.error('Viga URL-i parsimisel:', e);
                return null;
            }
        }

        function copyPromptAndSpec() {
            const promptTextarea = document.getElementById('promptTextarea');
            const swaggerTextarea = document.getElementById('swaggerDocOutput');
            const copyButton = document.getElementById('copyButton');
            const originalButtonText = copyButton.innerHTML;

            // Only proceed if there's content to copy
            if (!swaggerTextarea.value.trim()) {
                alert('OpenAPI spetsifikatsioon puudub. Palun hangi spetsifikatsioon enne kopeerimist.');
                return;
            }

            // Get the prompt text from the textarea (which exists for both admins and non-admins)
            // For non-admins, this is a hidden textarea that still contains the prompt text
            const promptText = promptTextarea.value;

            // Combine the content from both textareas
            const combinedText = promptText + '\n\n' + swaggerTextarea.value;

            // Use the modern Clipboard API if available
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(combinedText)
                    .then(() => {
                        // Visual feedback that copy was successful
                        copyButton.innerHTML = '<i class="bi bi-check"></i> Kopeeritud!';
                        setTimeout(() => {
                            copyButton.innerHTML = originalButtonText;
                        }, 1500);
                    })
                    .catch(err => {
                        console.error('Teksti kopeerimine ebaõnnestus: ', err);
                        // Fallback to the older method
                        fallbackCopyTextToClipboard(combinedText);
                    });
            } else {
                // Fallback for browsers that don't support the Clipboard API
                fallbackCopyTextToClipboard(combinedText);
            }

            // Fallback copy method using execCommand
            function fallbackCopyTextToClipboard(text) {
                // Create a temporary textarea
                const tempTextarea = document.createElement('textarea');
                tempTextarea.style.position = 'fixed';
                tempTextarea.style.left = '-9999px';
                tempTextarea.style.top = '0';
                tempTextarea.value = text;
                document.body.appendChild(tempTextarea);

                // Select and copy the text
                tempTextarea.focus();
                tempTextarea.select();

                try {
                    const successful = document.execCommand('copy');
                    if (successful) {
                        // Visual feedback that copy was successful
                        copyButton.innerHTML = '<i class="bi bi-check"></i> Kopeeritud!';
                        setTimeout(() => {
                            copyButton.innerHTML = originalButtonText;
                        }, 1500);
                    } else {
                        alert('Teksti kopeerimine ebaõnnestus');
                    }
                } catch (err) {
                    console.error('Teksti kopeerimine ebaõnnestus: ', err);
                    alert('Teksti kopeerimine ebaõnnestus: ' + err);
                } finally {
                    // Remove the temporary textarea
                    document.body.removeChild(tempTextarea);
                }
            }
        }

        // Image modal functionality for comment images
        function showImageModal(modalId, imageUrl, altText) {
            // Remove any existing modal
            const existingModal = document.querySelector('.image-modal-backdrop');
            if (existingModal) {
                existingModal.remove();
            }

            // Create modal backdrop
            const modalBackdrop = document.createElement('div');
            modalBackdrop.className = 'image-modal-backdrop';
            modalBackdrop.onclick = function() {
                closeImageModal();
            };

            // Create close button
            const closeButton = document.createElement('span');
            closeButton.className = 'image-modal-close';
            closeButton.innerHTML = '&times;';
            closeButton.onclick = function(e) {
                e.stopPropagation();
                closeImageModal();
            };

            // Create image element
            const modalImage = document.createElement('img');
            modalImage.src = imageUrl;
            modalImage.alt = altText || 'Suurendatud pilt';
            modalImage.className = 'image-modal-content';
            modalImage.onclick = function(e) {
                e.stopPropagation(); // Prevent closing when clicking on image
            };

            // Handle image load error
            modalImage.onerror = function() {
                const errorDiv = document.createElement('div');
                errorDiv.style.color = 'white';
                errorDiv.style.textAlign = 'center';
                errorDiv.style.fontSize = '18px';
                errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle"></i><br>Pilti ei õnnestunud laadida';

                modalBackdrop.innerHTML = '';
                modalBackdrop.appendChild(closeButton);
                modalBackdrop.appendChild(errorDiv);
            };

            // Append elements
            modalBackdrop.appendChild(closeButton);
            modalBackdrop.appendChild(modalImage);

            // Add to document
            document.body.appendChild(modalBackdrop);

            // Add keyboard event listener for ESC key
            document.addEventListener('keydown', handleImageModalKeydown);
        }

        function closeImageModal() {
            const modal = document.querySelector('.image-modal-backdrop');
            if (modal) {
                modal.remove();
            }
            // Remove keyboard event listener
            document.removeEventListener('keydown', handleImageModalKeydown);
        }

        function handleImageModalKeydown(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        }

        // Markdown parser function (handles both markdown and existing HTML)
        function parseMarkdown(text) {
            if (!text) return '';

            let html = text;

            // Check if the text already contains HTML tags (like <p>, <img>, etc.)
            if (html.includes('<img') || html.includes('<p>') || html.includes('</p>') || html.includes('<br')) {

                // If it contains img tags, enhance them for our modal functionality
                html = html.replace(/<img\s+([^>]*?)src=["']([^"']+)["']([^>]*?)alt=["']([^"']*?)["']([^>]*?)\/?>/gi, function(match, beforeSrc, src, betweenSrcAlt, alt, afterAlt) {

                    const modalId = 'imageModal_' + Math.random().toString(36).substr(2, 9);
                    return '<div class="image-preview-container mt-2 mb-2">' +
                        '<img src="' + src + '" alt="' + alt + '" ' +
                        'class="comment-image img-fluid rounded shadow-sm" ' +
                        'style="max-height: 200px; cursor: pointer; border: 1px solid #dee2e6; opacity: 0; transition: opacity 0.3s ease;" ' +
                        'onclick="showImageModal(\'' + modalId + '\', \'' + src + '\', \'' + alt + '\')" ' +
                        'onload="this.style.opacity=1" ' +
                        'onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'block\'">' +
                        '<div class="image-error text-muted small" style="display: none; padding: 10px; border: 1px dashed #ccc; border-radius: 5px;">' +
                        '<i class="bi bi-image"></i> Pilti ei õnnestunud laadida' +
                        '</div>' +
                        '</div>';
                });

                // Also handle the alternative pattern where alt comes before src
                html = html.replace(/<img\s+([^>]*?)alt=["']([^"']*?)["']([^>]*?)src=["']([^"']+)["']([^>]*?)\/?>/gi, function(match, beforeAlt, alt, betweenAltSrc, src, afterSrc) {

                    const modalId = 'imageModal_' + Math.random().toString(36).substr(2, 9);
                    return '<div class="image-preview-container mt-2 mb-2">' +
                        '<img src="' + src + '" alt="' + alt + '" ' +
                        'class="comment-image img-fluid rounded shadow-sm" ' +
                        'style="max-height: 200px; cursor: pointer; border: 1px solid #dee2e6; opacity: 0; transition: opacity 0.3s ease;" ' +
                        'onclick="showImageModal(\'' + modalId + '\', \'' + src + '\', \'' + alt + '\')" ' +
                        'onload="this.style.opacity=1" ' +
                        'onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'block\'">' +
                        '<div class="image-error text-muted small" style="display: none; padding: 10px; border: 1px dashed #ccc; border-radius: 5px;">' +
                        '<i class="bi bi-image"></i> Pilti ei õnnestunud laadida' +
                        '</div>' +
                        '</div>';
                });

                return html;
            }

            // Escape HTML first
            html = html.replace(/&/g, '&amp;');
            html = html.replace(/</g, '&lt;');
            html = html.replace(/>/g, '&gt;');

            // Convert line breaks
            html = html.replace(/\n/g, '<br>');

            // Headers
            html = html.replace(/^#### (.*$)/gim, '<h4>$1</h4>');
            html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
            html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
            html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');

            // Bold
            html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/__(.*?)__/g, '<strong>$1</strong>');

            // Italic
            html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
            html = html.replace(/_(.*?)_/g, '<em>$1</em>');

            // Code blocks
            html = html.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');

            // Inline code
            html = html.replace(/`(.*?)`/g, '<code>$1</code>');

            // Images - handle before links to avoid conflicts
            html = html.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, function(match, alt, src) {
                const modalId = 'imageModal_' + Math.random().toString(36).substr(2, 9);
                return '<div class="image-preview-container mt-2 mb-2">' +
                    '<img src="' + src + '" alt="' + alt + '" ' +
                    'class="comment-image img-fluid rounded shadow-sm" ' +
                    'style="max-height: 200px; cursor: pointer; border: 1px solid #dee2e6; opacity: 0; transition: opacity 0.3s ease;" ' +
                    'onclick="showImageModal(\'' + modalId + '\', \'' + src + '\', \'' + alt + '\')" ' +
                    'onload="this.style.opacity=1" ' +
                    'onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'block\'">' +
                    '<div class="image-error text-muted small" style="display: none; padding: 10px; border: 1px dashed #ccc; border-radius: 5px;">' +
                    '<i class="bi bi-image"></i> Pilti ei õnnestunud laadida' +
                    '</div>' +
                    '</div>';
            });

            // Links
            html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');

            // Unordered lists
            html = html.replace(/^\* (.+)$/gm, '<li>$1</li>');
            html = html.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');

            // Ordered lists
            html = html.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');
            html = html.replace(/(<li>.*<\/li>)/s, function(match) {
                if (match.includes('<ul>')) return match;
                return '<ol>' + match + '</ol>';
            });

            // Blockquotes
            html = html.replace(/^> (.+)$/gm, '<blockquote>$1</blockquote>');

            // Horizontal rules
            html = html.replace(/^---$/gm, '<hr>');

            return html;
        }

        // Process all comments on page load
        function processComments() {
            const commentElements = document.querySelectorAll('.comment-text[data-raw-comment]');

            commentElements.forEach(function(element, index) {

                const rawComment = element.getAttribute('data-raw-comment');

                if (rawComment) {
                    const processedHtml = parseMarkdown(rawComment);

                    // Clear existing content and set new HTML
                    element.innerHTML = '';
                    element.innerHTML = processedHtml;

                } else {
                    console.log('No raw comment data found for element', index + 1);
                }
            });

        }

        // Process comments in modal
        function processModalComments() {
            const modalCommentElements = document.querySelectorAll('#commentsContainer .comment-text[data-raw-comment]');

            modalCommentElements.forEach(function(element) {
                const rawComment = element.getAttribute('data-raw-comment');

                if (rawComment) {
                    const processedHtml = parseMarkdown(rawComment);
                    element.innerHTML = processedHtml;
                }
            });
        }

        // Flag to prevent infinite sync loops
        let isSyncing = false;

        // Sync criteria checkboxes between modal and main page
        function syncCriteriaCheckboxes(criteriaId, isChecked) {
            if (!criteriaId || isSyncing) {
                console.log(`Sync skipped - criteriaId: ${criteriaId}, isSyncing: ${isSyncing}`);
                return;
            }

            console.log(`🔄 Syncing criterion ${criteriaId} to ${isChecked ? 'checked' : 'unchecked'}`);
            isSyncing = true;

            try {
                // Update main page checkbox
                const mainPageCheckbox = document.querySelector(`#requiredCriteria #criterion_${criteriaId}`);
                console.log(`Main page checkbox found:`, mainPageCheckbox);
                if (mainPageCheckbox) {
                    console.log(`Main page checkbox current state: ${mainPageCheckbox.checked}, target: ${isChecked}`);
                    if (mainPageCheckbox.checked !== isChecked) {
                        console.log(`✅ Updating main page checkbox ${criteriaId} to ${isChecked}`);
                        mainPageCheckbox.checked = isChecked;
                        
                        // Show save button for main page
                        const saveButton = document.querySelector('#studentCriteriaForm .btn-primary');
                        if (saveButton) {
                            saveButton.hidden = false;
                        }
                    }
                } else {
                    console.log(`❌ Main page checkbox not found: #requiredCriteria #criterion_${criteriaId}`);
                }

                // Update modal checkbox (only if modal is open)
                const modalCheckbox = document.querySelector(`#checkboxesContainer #criterion_${criteriaId}`);
                console.log(`Modal checkbox found:`, modalCheckbox);
                if (modalCheckbox) {
                    console.log(`Modal checkbox current state: ${modalCheckbox.checked}, target: ${isChecked}`);
                    if (modalCheckbox.checked !== isChecked) {
                        console.log(`✅ Updating modal checkbox ${criteriaId} to ${isChecked}`);
                        modalCheckbox.checked = isChecked;
                        
                        // Update submit button state in modal if needed
                        if (typeof updateButtonState === 'function') {
                            updateButtonState();
                        }
                    }
                } else {
                    console.log(`❌ Modal checkbox not found: #checkboxesContainer #criterion_${criteriaId}`);
                }
            } catch (error) {
                console.error('Error in syncCriteriaCheckboxes:', error);
            } finally {
                // Reset sync flag after a short delay
                setTimeout(() => {
                    isSyncing = false;
                    console.log(`🔓 Sync flag reset for criterion ${criteriaId}`);
                }, 50);
            }
        }
    </script>