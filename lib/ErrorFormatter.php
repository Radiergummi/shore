<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 04.10.2018
 * Time: 10:49
 */

namespace Shore\Framework;

use Throwable;

class ErrorFormatter
{
    public const CONTEXT_LINES = 15;

    protected $template = <<<TEMPLATE
<style>
    * {
        box-sizing: border-box;
    }

    body {
        display: flex;
        flex-wrap: wrap;
        height: 100vh;
        margin: 0;
        font-family: sans-serif;
        background: #404040;
        color: #d0d0f0;
        overflow: hidden;
    }
    
    .error-details,
    .error-source-code {
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
        font-weight: bold;
    }

    .code-extract {
        height: 100%%;
        margin: 1rem 0 0;
        overflow-x: auto;
    }

    .line {
        display: block;
        padding: 0 1rem;
        line-height: 1.6;
        font-size: 0.9rem;
    }
    
    .line-number {
        opacity: 0.25;
        user-select: none;
    }

    .line-origin {
        color: #e94854;
        background: #303030
    }
    
    .line:hover {
        background: #252525;
        transition: all 0.125s;
    }
    
    .line-origin .line-number {
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
    <pre class="code-extract">%s</pre>
</article>
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
        $type = get_class($exception);
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

        foreach (array_slice($stackTrace, 1) as $index => $frame) {
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
                $frame['file'],
                $frame['line'],
                substr($frame['file'], strlen(ROOT)),
                $frame['line']
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

        $firstLine = $line - static::CONTEXT_LINES;
        $lastLine = $line + static::CONTEXT_LINES;

        for ($lineNumber = $firstLine; $lineNumber <= $lastLine; $lineNumber++) {
            $currentLine = $lines[$lineNumber];

            $extract .= sprintf(
                '<span class="%s"><span class="line-number">%d</span><code>%s</code></span>',
                $lineNumber === $line - 1 ? 'line line-origin' : 'line',
                $lineNumber + 1,
                $currentLine
            );
        }

        return $extract;
    }
}
