<?php

namespace Shore\Framework;

use Throwable;

class ErrorFormatter
{
    protected $template = <<<TEMPLATE
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/languages/php.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/atom-one-dark.min.css">
<script>
    /* global hljs */
    hljs.initHighlightingOnLoad();
    document.addEventListener('DOMContentLoaded', () => {
        const sourceContainer = document.querySelector('.code-extract');
        const targetLine = document.querySelector('.line-origin');
        
        if (!targetLine) {
          return;
        }
        
        sourceContainer.scrollTop = targetLine.offsetTop - (targetLine.offsetHeight * 10);
    });
</script>
<style>
    * {
        box-sizing: border-box;
    }
    
    html,
    body {
        height: 100vh;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: sans-serif;
        overflow: hidden;
    }
    
    .main-content {
        display: flex;
        flex-wrap: wrap;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: #404040;
        color: #d0d0f0;
    }
    
    .error-details,
    .error-source-code {
        position: relative;
        height: 100vh;
    }
    
    .error-details {
        flex: 0 0 60%%;
        max-width: 60%%;
    }
    
    .error-source-code {
        flex: 0 0 40%%;
        max-width: 40%%;
    }
    
    .error-details {
        display: flex;
        flex-direction: column;
    }
    
    .error-meta {
        padding: 1rem;
    }
    
    .error-type {
        margin: 0;
        font-size: 3rem;
        color: #e94854;
    }
    
    .error-message {
        font-size: 1.2rem;
    }
    
    .error-trace {
        margin-top: auto;
        padding: 1rem;
        overflow-x: auto;
        background: #353535;
    }
    
    .error-trace-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .stack-frame {
        margin: 0;
        padding: 0.5rem;
        background: #252525;
        border-left: 1px solid transparent;
        transition: all 0.125s;
   }
    
    .stack-frame + .stack-frame {
        margin-top: 0.25rem;
    }
    
    .stack-frame:hover {
        border-left: 4px solid #888bcd;
          background: #272727;
  }
    
    .stack-frame-inner {
        display: flex;
        align-items: center;
    }
    
    .stack-frame .stack-position {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        width: 1.5rem;
        height: 1.5rem;
        margin-right: 0.5rem;
        padding: 0.25rem;
        border-radius: 50%%;
        background: #888bcd;
        color: #fff;
        font-size: 0.75rem;
        user-select: none;
    }
    
    .stack-frame .stack-title {
        flex: 1 0 auto;
        margin: 0;
    }
    
    .stack-frame .stack-path {
        display: flex;
    }
    
    .stack-frame .stack-path .stack-file:not(:empty)::after {
        content: ':';
    }
    
    .error-environment {
        overflow: auto;
        padding: 1rem 0;
        max-height: 50vh;
    }
    
    .error-environment .environment-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .error-environment .environment-list .env {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.25rem 1.5rem;
        transition: all 0.125s;
    }
    
    .error-environment .environment-list .env:hover {
        background: rgba(208,208,240,0.05);
    }
    
    .error-environment .environment-list .env:hover .key {
        background: #888bcd;
    }
    
    .error-environment .environment-list .env + .env {
        border-top: 1px solid rgba(208,208,240,0.1);
    }

    .error-environment .environment-list .env .key {
        margin-right: 0.5rem;
        padding: 2px 0.5rem;
        border-radius: 2px;
        background: rgba(208,208,240,0.5);
        color: #404040;
        font-size: 0.75rem;
        transition: all 0.125s;
    }
    
    .error-source-code {
        background: #202020;
    }
    
    .code-extract-path {
        display: block;
        margin: 0 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #303030;
        font-size: 1.2rem;
        line-height: 1;
        font-weight: bold;
    }

    .code-extract {
        height: calc(100%% - 4rem);
        margin: 1rem 0 0;
        overflow-x: auto;
        counter-reset: lineCount;
    }
    
    .code-extract .hljs {
        background: #202020;
        padding: 0;
    }

    .line {
        display: block;
        padding: 0 1rem;
        line-height: 1.6;
        font-size: 0.9rem;
        counter-increment: lineCount;
        white-space: pre;
    }
    
    .line::before {
        display: inline-block;
        width: 2rem;
        content: counter(lineCount);
        text-align: right;
        margin-right: 0.5rem;
        opacity: 0.25;
        user-select: none;
    }
    
    .line-number {
    }

    .line-origin {
        background: rgba(177,38,34,0.5)
    }
    
    .line:hover {
        background: #252525;
        transition: all 0.125s;
    }
    
    .line-origin::before {
        opacity: 0.75;
    }
    
    ::-webkit-scrollbar {
        width: 0.5rem;
        height: 0.5rem;
    }
    
    ::-webkit-scrollbar-corner {
        background: transparent;
    }

    ::-webkit-scrollbar-thumb {
        border-radius: 10px;
        background: rgba(255,255,255,0.2);
    }
</style>
<main class="main-content">
    <article class="error-details">
        <header class="error-meta">
            <h1 class="error-type">%s</h1>
            <p class="error-message">%s</p>        
        </header>
        <section class="error-trace">
            <ul class="error-trace-list">%s</ul>
        </section>
        <section class="error-environment">
            <ul class="environment-list">%s</ul>
        </section>
    </article>
    <article class="error-source-code">
        <code class="code-extract-path">%s</code>
        <pre class="code-extract"><code class="code-extract-lines language-php">%s</code></pre>
    </article>
</main>
TEMPLATE;

    /**
     * Formats an error
     *
     * @param \Throwable $exception
     *
     * @return string
     */
    public function __invoke(Throwable $exception): string
    {
        $file = $exception->getFile();
        $line = $exception->getLine();
        $typeClass = explode('\\', get_class($exception));
        $type = array_pop($typeClass);
        $message = $exception->getMessage();
        $trace = $this->buildTrace($exception->getTrace());
        $environment = $this->getEnvironment();
        $context = $this->getCodeContext($file, $line);

        return sprintf(
            $this->template,
            $type,
            $message,
            $trace,
            $environment,
            $file,
            $context
        );
    }

    protected function buildTrace(array $stackTrace): string
    {
        $traceData = '';

        foreach ($stackTrace as $index => $frame) {
            $classSegments = explode('\\', $frame['class'] ?? '');
            $className = strpos($frame['class'], 'class@anonymous' === false)
                ? '[anonymous]'
                : array_pop($classSegments);

            $traceData .= sprintf(
                '<li class="stack-frame">
                            <code class="stack-frame-inner">
                                <span class="stack-position">%d</span>
                                <h3 class="stack-title">
                                    <span class="stack-class">%s</span><span class="stack-method">%s</span>
                                </h3>
                                <span class="stack-path" title="%s:%d">
                                    <span class="stack-file">%s</span>
                                    <span class="stack-line">%d</span>
                                </span>
                            </code>
                        </li>',
                $index,
                $className,
                $frame['type'] . $frame['function'],
                $frame['file'] ?? '',
                $frame['line'] ?? 0,
                substr($frame['file'] ?? '', strlen(ROOT)),
                $frame['line'] ?? 0
            );
        }

        return $traceData;
    }

    protected function getEnvironment()
    {
        $environmentData = '';

        foreach (array_merge($_GET, $_POST, $_SERVER) as $key => $value) {
            $environmentData .= sprintf(
                '<li class="env"><span class="key">%s</span><code class="value">%s</code></li>',
                $key,
                $value
            );
        }

        return $environmentData;
    }

    protected function getCodeContext(string $path, int $line = 0): string
    {
        if (! is_readable($path)) {
            return '';
        }

        $lines = file($path);
        $extract = '';

        for ($lineNumber = 0; $lineNumber < count($lines); $lineNumber++) {
            $currentLine = $lines[$lineNumber];

            $extract .= sprintf(
                '<div id="line-%d" class="%s php">%s</div>',
                $lineNumber + 1,
                $lineNumber === $line - 1 ? 'line line-origin' : 'line',
                htmlentities($currentLine)
            );
        }

        return $extract;
    }
}
