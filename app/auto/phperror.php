<?php

declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


define('ERROR_HANDLER_CONFIG', [
    'show_environment' => true,         
    'show_request' => true,             
    'show_backtrace' => true,           
    'show_code_snippet' => true,        
    'snippet_lines' => 7,               
    'show_memory' => true,              
    'show_timestamp' => true,           
    'show_version' => true,             
    'show_extensions' => false,         
    'dark_mode' => true,                
    'allow_ajax' => true,               
    'log_errors' => true,               
    'log_file' => 'error_log.log',
    'email_errors' => false,
    'email_to' => 'admin@example.com',  
    'email_from' => 'errors@example.com',
    'email_subject' => 'Critical Error Occurred'
]);

set_exception_handler(function (Throwable $e) {
    if (isAjaxRequest() && ERROR_HANDLER_CONFIG['allow_ajax']) {
        sendJsonError($e);
    } else {
        renderFatalError($e);
    }
    logError($e);
    if (ERROR_HANDLER_CONFIG['email_errors'] && isCriticalError($e)) {
        sendErrorEmail($e);
    }
    exit(1);
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    $error = new ErrorException($errstr, 0, $errno, $errfile, $errline);

    if (isAjaxRequest() && ERROR_HANDLER_CONFIG['allow_ajax']) {
        sendJsonError($error);
    } else {
        renderError($errno, $errstr, $errfile, $errline);
    }
    logError($error);
    exit(1);
});

register_shutdown_function(function () {
    if ($error = error_get_last()) {
        $exception = new ErrorException(
            $error['message'],
            0,
            $error['type'],
            $error['file'],
            $error['line']
        );
        echo "
    <style>
    :root {
        --notice: #00e5ff;
        --warning: #FFA500;
        --fatal: #FF3D3D;
        --exception: #BA68C8;
        --bg-dark: #0D0F15;
        --bg-light: #161925;
        --card-bg: rgba(25, 30, 45, 0.95);
        --text-primary: #F0F4FF;
        --text-secondary: #B0B8D0;
        --border-radius: 16px;
        --glow-opacity: 0.15;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, var(--bg-dark), var(--bg-light));
        color: var(--text-primary);
        min-height: 100vh;
        padding: 2rem 1rem;
        line-height: 1.6;
    }

    .error-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
        perspective: 1000px;
    }

    .error-card {
        width: 100%;
        background: var(--card-bg);
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        transform: translateY(30px) rotateX(-5deg);
        opacity: 0;
        animation: cardEntrance 0.8s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        transition: all 0.3s ease;
        position: relative;
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .error-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: var(--border-radius);
        padding: 2px;
        background: linear-gradient(135deg, rgba(255,255,255,0.2), transparent);
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }

    .error-card:hover {
        transform: translateY(-5px) rotateX(0) scale(1.02);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    }

    .error-header {
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        position: relative;
        overflow: hidden;
    }

    .error-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 2rem;
        right: 2rem;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    }

    .error-icon {
        font-size: 1.8rem;
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: rgba(0, 0, 0, 0.3);
        box-shadow: 0 0 15px currentColor;
    }

    .error-title {
        font-size: 1.5rem;
        font-weight: 600;
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }

    .error-type {
        font-size: 0.9rem;
        font-weight: 500;
        opacity: 0.8;
        letter-spacing: 1px;
    }

    .error-body {
        padding: 0 2rem 2rem;
    }

    .error-message {
        font-size: 1.1rem;
        line-height: 1.8;
        margin: 1.5rem 0;
        padding: 1.5rem;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        border-left: 4px solid;
        font-family: 'JetBrains Mono', monospace;
        position: relative;
        overflow: hidden;
    }

    .error-message::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 100%;
        background: linear-gradient(90deg, rgba(255,255,255,0.03), transparent);
    }

    .error-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .detail-box {
        background: rgba(0, 0, 0, 0.2);
        padding: 1rem 1.2rem;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.05);
        position: relative;
        overflow: hidden;
    }

    .detail-box:hover {
        background: rgba(0, 0, 0, 0.3);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .detail-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 3px;
        height: 100%;
        background: currentColor;
        opacity: 0.3;
    }

    .detail-label {
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .detail-value {
        font-size: 0.95rem;
        font-weight: 500;
        word-break: break-word;
        font-family: 'JetBrains Mono', monospace;
    }

    .error-stack {
        margin-top: 2rem;
    }

    .stack-title {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        font-size: 1rem;
        margin-bottom: 1rem;
        color: var(--text-secondary);
    }

    .stack-content {
        background: rgba(0, 0, 0, 0.2);
        padding: 1.5rem;
        border-radius: 8px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.85rem;
        line-height: 1.7;
        white-space: pre-wrap;
        overflow-x: auto;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    /* Code snippet styling */
    .code-snippet {
        margin-top: 1.5rem;
    }
    
    .code-snippet-title {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        font-size: 1rem;
        margin-bottom: 1rem;
        color: var(--text-secondary);
    }
    
    .code-content {
        background: rgba(0, 0, 0, 0.2);
        padding: 1rem;
        border-radius: 8px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.85rem;
        line-height: 1.5;
        overflow-x: auto;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    .code-line {
        display: flex;
        margin: 0.1rem 0;
    }
    
    .line-number {
        color: var(--text-secondary);
        min-width: 3rem;
        text-align: right;
        padding-right: 1rem;
        user-select: none;
    }

    /* Toggle sections */
    .toggle-section {
        margin-top: 2rem;
    }
    
    .toggle-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.8rem 1rem;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .toggle-header:hover {
        background: rgba(0, 0, 0, 0.3);
    }
    
    .toggle-title {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        font-size: 1rem;
        color: var(--text-secondary);
    }
    
    .toggle-icon {
        transition: transform 0.3s ease;
    }
    
    .toggle-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        background: rgba(0, 0, 0, 0.1);
        border-radius: 0 0 8px 8px;
    }
    
    .toggle-section.active .toggle-icon {
        transform: rotate(180deg);
    }
    
    .toggle-section.active .toggle-content {
        max-height: 1000px;
        padding: 1rem;
    }

    /* Error type styles */
    .notice {
        --color: var(--notice);
        --glow: rgba(0, 229, 255, var(--glow-opacity));
    }
    .warning {
        --color: var(--warning);
        --glow: rgba(255, 165, 0, var(--glow-opacity));
    }
    .fatal {
        --color: var(--fatal);
        --glow: rgba(255, 61, 61, var(--glow-opacity));
    }
    .exception {
        --color: var(--exception);
        --glow: rgba(186, 104, 200, var(--glow-opacity));
    }

    .error-icon { color: var(--color); }
    .error-message { border-color: var(--color); }
    .detail-box { color: var(--color); }

    /* Animations */
    @keyframes cardEntrance {
        from { opacity: 0; transform: translateY(30px) rotateX(-10deg); }
        to { opacity: 1; transform: translateY(0) rotateX(0); }
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    @keyframes float {
        0% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
        100% { transform: translateY(0); }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .error-details {
            grid-template-columns: 1fr;
        }
        
        .error-header {
            padding: 1.2rem 1.5rem;
        }
        
        .error-body {
            padding: 0 1.5rem 1.5rem;
        }
        
        .error-message {
            padding: 1rem;
            font-size: 1rem;
        }
    }

    /* Glow effects */
    .error-card {
        box-shadow: 0 0 30px var(--glow), 0 12px 40px rgba(0, 0, 0, 0.4);
    }

    /* Animation delays */
    .error-card.notice { animation-delay: 0.1s; }
    .error-card.warning { animation-delay: 0.2s; }
    .error-card.fatal { animation-delay: 0.3s; }
    .error-card.exception { animation-delay: 0.4s; }
    </style>
        ";
        if (isAjaxRequest() && ERROR_HANDLER_CONFIG['allow_ajax']) {
            sendJsonError($exception);
        } else {
            renderFatalError($exception);
        }
        logError($exception);
        if (ERROR_HANDLER_CONFIG['email_errors'] && isCriticalError($exception)) {
            sendErrorEmail($exception);
        }
        exit(1);
    }
});

function isAjaxRequest(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function isCriticalError(Throwable $e): bool
{
    return $e instanceof Error ||
        ($e instanceof ErrorException && in_array($e->getSeverity(), [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR]));
}

function sendJsonError(Throwable $e): void
{
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'type' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => ERROR_HANDLER_CONFIG['show_backtrace'] ? $e->getTrace() : null,
        'code' => $e->getCode(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}

function logError(Throwable $e): void
{
    if (!ERROR_HANDLER_CONFIG['log_errors']) return;

    $logEntry = sprintf(
        "[%s] %s: %s in %s on line %d\nStack trace:\n%s\n\n",
        date('Y-m-d H:i:s'),
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );

    file_put_contents(ERROR_HANDLER_CONFIG['log_file'], $logEntry, FILE_APPEND);
}

function sendErrorEmail(Throwable $e): void
{
    $subject = ERROR_HANDLER_CONFIG['email_subject'];
    $message = "A critical error occurred on your website:\n\n";
    $message .= "Error: " . get_class($e) . "\n";
    $message .= "Message: " . $e->getMessage() . "\n";
    $message .= "File: " . $e->getFile() . "\n";
    $message .= "Line: " . $e->getLine() . "\n";
    $message .= "Time: " . date('Y-m-d H:i:s') . "\n\n";
    $message .= "Stack Trace:\n" . $e->getTraceAsString() . "\n\n";
    $message .= "Request URI: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
    $message .= "IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? '') . "\n";

    $headers = "From: " . ERROR_HANDLER_CONFIG['email_from'] . "\r\n";

    @mail(ERROR_HANDLER_CONFIG['email_to'], $subject, $message, $headers);
}

function getCodeSnippet(string $file, int $line, int $linesAround = 5): ?string
{
    if (!ERROR_HANDLER_CONFIG['show_code_snippet'] || !file_exists($file)) {
        return null;
    }

    $fileLines = file($file);
    $startLine = max(0, $line - $linesAround - 1);
    $endLine = min(count($fileLines), $line + $linesAround);
    $snippet = '';

    for ($i = $startLine; $i < $endLine; $i++) {
        $currentLine = $i + 1;
        $lineContent = htmlspecialchars($fileLines[$i]);
        $highlight = ($currentLine == $line) ? 'style="background: rgba(255, 100, 100, 0.2);"' : '';
        $snippet .= sprintf(
            '<div class="code-line" %s><span class="line-number">%d</span> %s</div>',
            $highlight,
            $currentLine,
            $lineContent
        );
    }

    return $snippet;
}

function getRequestDetails(): array
{
    return [
        'Method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
        'URI' => $_SERVER['REQUEST_URI'] ?? 'Command Line',
        'Protocol' => $_SERVER['SERVER_PROTOCOL'] ?? '',
        'IP' => $_SERVER['REMOTE_ADDR'] ?? '',
        'User Agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'Referer' => $_SERVER['HTTP_REFERER'] ?? '',
        'Query String' => $_SERVER['QUERY_STRING'] ?? '',
        'POST Data' => !empty($_POST) ? $_POST : null,
        'GET Data' => !empty($_GET) ? $_GET : null
    ];
}

function getEnvironmentDetails(): array
{
    return [
        'PHP Version' => PHP_VERSION,
        'OS' => PHP_OS,
        'Server' => $_SERVER['SERVER_SOFTWARE'] ?? '',
        'Host' => $_SERVER['HTTP_HOST'] ?? '',
        'Memory Usage' => memory_get_usage(true) / 1024 / 1024 . ' MB',
        'Peak Memory' => memory_get_peak_usage(true) / 1024 / 1024 . ' MB',
        'Include Path' => get_include_path(),
        'Loaded Extensions' => ERROR_HANDLER_CONFIG['show_extensions'] ? implode(', ', get_loaded_extensions()) : null,
        'Display Errors' => ini_get('display_errors'),
        'Error Reporting' => error_reporting(),
        'Timezone' => date_default_timezone_get(),
        'Current Time' => date('Y-m-d H:i:s')
    ];
}


function renderError(int $errno, string $errstr, string $errfile, int $errline): void
{
    echo <<<'HTML'

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Poppins:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<body>
    <div class="error-container">
HTML;
    echo <<<'HTML'
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.error-card').forEach(card => {
            card.addEventListener('dblclick', function() {
                const errorText = getErrorText(this);
                navigator.clipboard.writeText(errorText).then(() => {
                    const icon = this.querySelector('.error-icon i');
                    const originalClass = icon.className;
                    icon.className = 'fas fa-check';
                    setTimeout(() => {
                        icon.className = originalClass;
                    }, 2000);
                });
            });
        });
        
    });
    
    function getErrorText(card) {
        let text = '';
        
        const title = card.querySelector('.error-title span:first-child').textContent;
        const type = card.querySelector('.error-type').textContent;
        text += `${title} - ${type}\n\n`;
        
        const message = card.querySelector('.error-message').textContent;
        text += `Message: ${message}\n\n`;
        
        const details = card.querySelectorAll('.detail-box');
        details.forEach(detail => {
            const label = detail.querySelector('.detail-label').textContent.trim();
            const value = detail.querySelector('.detail-value').textContent.trim();
            text += `${label}: ${value}\n`;
        });
        
        const stack = card.querySelector('.stack-content');
        if (stack) {
            text += `\nStack Trace:\n${stack.textContent}\n`;
        }
        
        const requestSection = card.querySelector('.toggle-section:nth-of-type(1)');
        if (requestSection) {
            text += `\nRequest Details:\n`;
            const requestDetails = requestSection.querySelectorAll('.detail-box');
            requestDetails.forEach(detail => {
                const label = detail.querySelector('.detail-label').textContent.trim();
                const value = detail.querySelector('.detail-value').textContent.trim();
                text += `  ${label}: ${value}\n`;
            });
        }
        
        const envSection = card.querySelector('.toggle-section:nth-of-type(2)');
        if (envSection) {
            text += `\nEnvironment Details:\n`;
            const envDetails = envSection.querySelectorAll('.detail-box');
            envDetails.forEach(detail => {
                const label = detail.querySelector('.detail-label').textContent.trim();
                const value = detail.querySelector('.detail-value').textContent.trim();
                text += `  ${label}: ${value}\n`;
            });
        }
        
        return text;
    }
    </script>
</body>
</html>
HTML;
    $types = [
        E_NOTICE => 'Notice',
        E_WARNING => 'Warning',
        E_DEPRECATED => 'Deprecation',
        E_USER_NOTICE => 'User Notice',
        E_USER_WARNING => 'User Warning',
        E_USER_DEPRECATED => 'User Deprecation',
        E_STRICT => 'Strict Standards',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_PARSE => 'Parse Error'
    ];

    $errorType = $types[$errno] ?? 'Error';
    $class = match ($errno) {
        E_NOTICE, E_USER_NOTICE, E_STRICT => 'notice',
        E_WARNING, E_USER_WARNING, E_DEPRECATED, E_USER_DEPRECATED, E_COMPILE_WARNING, E_CORE_WARNING => 'warning',
        default => 'fatal'
    };

    $icon = match ($class) {
        'notice' => 'info-circle',
        'warning' => 'exclamation-triangle',
        default => 'radiation'
    };

    $codeSnippet = getCodeSnippet($errfile, $errline, ERROR_HANDLER_CONFIG['snippet_lines']);
    $requestDetails = ERROR_HANDLER_CONFIG['show_request'] ? getRequestDetails() : [];
    $environmentDetails = ERROR_HANDLER_CONFIG['show_environment'] ? getEnvironmentDetails() : [];

    echo <<<HTML
    <div class="error-card $class">
        <div class="error-header">
            <div class="error-icon">
                <i class="fas fa-$icon"></i>
            </div>
            <div class="error-title">
                <span>PHP $errorType</span>
                <span class="error-type">Code: $errno</span>
            </div>
        </div>
        <div class="error-body">
            <div class="error-message">$errstr</div>
            <div class="error-details">
                <div class="detail-box">
                    <span class="detail-label"><i class="fas fa-file-code"></i> File</span>
                    <span class="detail-value">$errfile</span>
                </div>
                <div class="detail-box">
                    <span class="detail-label"><i class="fas fa-code"></i> Line</span>
                    <span class="detail-value">$errline</span>
                </div>
                <div class="detail-box">
                    <span class="detail-label"><i class="fas fa-clock"></i> Time</span>
                    <span class="detail-value">{$environmentDetails['Current Time']}</span>
                </div>
            </div>
HTML;

    if ($codeSnippet) {
        echo <<<HTML
            <div class="code-snippet">
                <div class="code-snippet-title">
                    <i class="fas fa-code"></i> Code Snippet (Line $errline)
                </div>
                <div class="code-content">$codeSnippet</div>
            </div>
HTML;
    }

    if (!empty($requestDetails)) {
        echo <<<HTML
            <div class="toggle-section">
                <div class="toggle-header" onclick="this.parentElement.classList.toggle('active')">
                    <div class="toggle-title">
                        <i class="fas fa-globe"></i> Request Details
                    </div>
                    <div class="toggle-icon">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                <div class="toggle-content">
                    <div class="error-details">
HTML;

        foreach ($requestDetails as $label => $value) {
            if ($value === null) continue;

            $formattedValue = is_array($value)
                ? '<pre>' . htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT)) . '</pre>'
                : htmlspecialchars($value);

            echo <<<HTML
                        <div class="detail-box">
                            <span class="detail-label"><i class="fas fa-tag"></i> $label</span>
                            <span class="detail-value">$formattedValue</span>
                        </div>
HTML;
        }

        echo <<<HTML
                    </div>
                </div>
            </div>
HTML;
    }

    if (!empty($environmentDetails)) {
        echo <<<HTML
            <div class="toggle-section">
                <div class="toggle-header" onclick="this.parentElement.classList.toggle('active')">
                    <div class="toggle-title">
                        <i class="fas fa-server"></i> Environment Details
                    </div>
                    <div class="toggle-icon">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                <div class="toggle-content">
                    <div class="error-details">
HTML;

        foreach ($environmentDetails as $label => $value) {
            if ($value === null) continue;

            $formattedValue = is_array($value)
                ? '<pre>' . htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT)) . '</pre>'
                : htmlspecialchars($value);

            echo <<<HTML
                        <div class="detail-box">
                            <span class="detail-label"><i class="fas fa-tag"></i> $label</span>
                            <span class="detail-value">$formattedValue</span>
                        </div>
HTML;
        }

        echo <<<HTML
                    </div>
                </div>
            </div>
HTML;
    }

    echo <<<HTML
        </div>
    </div>
HTML;
}

function renderFatalError(Throwable $e): void
{
    echo <<<'HTML'

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Poppins:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<body>
    <div class="error-container">
HTML;
    echo <<<'HTML'
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.error-card').forEach(card => {
            card.addEventListener('dblclick', function() {
                const errorText = getErrorText(this);
                navigator.clipboard.writeText(errorText).then(() => {
                    const icon = this.querySelector('.error-icon i');
                    const originalClass = icon.className;
                    icon.className = 'fas fa-check';
                    setTimeout(() => {
                        icon.className = originalClass;
                    }, 2000);
                });
            });
        });
        
    });
    
    function getErrorText(card) {
        let text = '';
        
        const title = card.querySelector('.error-title span:first-child').textContent;
        const type = card.querySelector('.error-type').textContent;
        text += `${title} - ${type}\n\n`;
        
        const message = card.querySelector('.error-message').textContent;
        text += `Message: ${message}\n\n`;
        
        const details = card.querySelectorAll('.detail-box');
        details.forEach(detail => {
            const label = detail.querySelector('.detail-label').textContent.trim();
            const value = detail.querySelector('.detail-value').textContent.trim();
            text += `${label}: ${value}\n`;
        });
        
        const stack = card.querySelector('.stack-content');
        if (stack) {
            text += `\nStack Trace:\n${stack.textContent}\n`;
        }
        
        const requestSection = card.querySelector('.toggle-section:nth-of-type(1)');
        if (requestSection) {
            text += `\nRequest Details:\n`;
            const requestDetails = requestSection.querySelectorAll('.detail-box');
            requestDetails.forEach(detail => {
                const label = detail.querySelector('.detail-label').textContent.trim();
                const value = detail.querySelector('.detail-value').textContent.trim();
                text += `  ${label}: ${value}\n`;
            });
        }
        
        const envSection = card.querySelector('.toggle-section:nth-of-type(2)');
        if (envSection) {
            text += `\nEnvironment Details:\n`;
            const envDetails = envSection.querySelectorAll('.detail-box');
            envDetails.forEach(detail => {
                const label = detail.querySelector('.detail-label').textContent.trim();
                const value = detail.querySelector('.detail-value').textContent.trim();
                text += `  ${label}: ${value}\n`;
            });
        }
        
        return text;
    }
    </script>
</body>
</html>
HTML;
    $class = 'exception';
    $icon = 'bug';
    $type = get_class($e);

    $codeSnippet = getCodeSnippet($e->getFile(), $e->getLine(), ERROR_HANDLER_CONFIG['snippet_lines']);
    $requestDetails = ERROR_HANDLER_CONFIG['show_request'] ? getRequestDetails() : [];
    $environmentDetails = ERROR_HANDLER_CONFIG['show_environment'] ? getEnvironmentDetails() : [];

    echo <<<HTML
    <div class="error-card $class">
        <div class="error-header">
            <div class="error-icon">
                <i class="fas fa-$icon"></i>
            </div>
            <div class="error-title">
                <span>$type</span>
                <span class="error-type">Uncaught Exception</span>
            </div>
        </div>
        <div class="error-body">
            <div class="error-message">{$e->getMessage()}</div>
            <div class="error-details">
                <div class="detail-box">
                    <span class="detail-label"><i class="fas fa-file-code"></i> File</span>
                    <span class="detail-value">{$e->getFile()}</span>
                </div>
                <div class="detail-box">
                    <span class="detail-label"><i class="fas fa-code"></i> Line</span>
                    <span class="detail-value">{$e->getLine()}</span>
                </div>
                <div class="detail-box">
                    <span class="detail-label"><i class="fas fa-barcode"></i> Code</span>
                    <span class="detail-value">{$e->getCode()}</span>
                </div>
                <div class="detail-box">
                    <span class="detail-label"><i class="fas fa-clock"></i> Time</span>
                    <span class="detail-value">{$environmentDetails['Current Time']}</span>
                </div>
            </div>
HTML;

    if ($codeSnippet) {
        echo <<<HTML
            <div class="code-snippet">
                <div class="code-snippet-title">
                    <i class="fas fa-code"></i> Code Snippet (Line {$e->getLine()})
                </div>
                <div class="code-content">$codeSnippet</div>
            </div>
HTML;
    }

    echo <<<HTML
            <div class="error-stack">
                <div class="stack-title">
                    <i class="fas fa-layer-group"></i> Stack Trace
                </div>
                <div class="stack-content">{$e->getTraceAsString()}</div>
            </div>
HTML;

    if (!empty($requestDetails)) {
        echo <<<HTML
            <div class="toggle-section">
                <div class="toggle-header" onclick="this.parentElement.classList.toggle('active')">
                    <div class="toggle-title">
                        <i class="fas fa-globe"></i> Request Details
                    </div>
                    <div class="toggle-icon">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                <div class="toggle-content">
                    <div class="error-details">
HTML;

        foreach ($requestDetails as $label => $value) {
            if ($value === null) continue;

            $formattedValue = is_array($value)
                ? '<pre>' . htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT)) . '</pre>'
                : htmlspecialchars($value);

            echo <<<HTML
                        <div class="detail-box">
                            <span class="detail-label"><i class="fas fa-tag"></i> $label</span>
                            <span class="detail-value">$formattedValue</span>
                        </div>
HTML;
        }

        echo <<<HTML
                    </div>
                </div>
            </div>
HTML;
    }

    if (!empty($environmentDetails)) {
        echo <<<HTML
            <div class="toggle-section">
                <div class="toggle-header" onclick="this.parentElement.classList.toggle('active')">
                    <div class="toggle-title">
                        <i class="fas fa-server"></i> Environment Details
                    </div>
                    <div class="toggle-icon">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                <div class="toggle-content">
                    <div class="error-details">
HTML;

        foreach ($environmentDetails as $label => $value) {
            if ($value === null) continue;

            $formattedValue = is_array($value)
                ? '<pre>' . htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT)) . '</pre>'
                : htmlspecialchars($value);

            echo <<<HTML
                        <div class="detail-box">
                            <span class="detail-label"><i class="fas fa-tag"></i> $label</span>
                            <span class="detail-value">$formattedValue</span>
                        </div>
HTML;
        }

        echo <<<HTML
                    </div>
                </div>
            </div>
HTML;
    }

    echo <<<HTML
        </div>
    </div>
HTML;
}
