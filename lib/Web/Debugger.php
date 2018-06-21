<?php
namespace Hyperframework\Web;

use Hyperframework\Common\ErrorException;
use Hyperframework\Common\Error;
use Hyperframework\Common\StackTraceFormatter;

class Debugger {
    private $error;
    private $trace;
    private $output;

    /**
     * @param object $error
     * @param string $output
     * @return void
     */
    public function execute($error, $output = null) {
        $this->error = $error;
        $this->output = $output;
        $this->trace = null;
        if ($this->error instanceof Error === false) {
            if ($this->error instanceof ErrorException) {
                $this->trace = $error->getSourceTrace();
            } else {
                $this->trace = $error->getTrace();
            }
        }
        if (Response::headersSent() === false) {
            Response::setHeader('Content-Type: text/html; charset=utf-8');
        }
        if ($this->error instanceof Error) {
            $type = htmlspecialchars(
                ucwords($error->getSeverityAsString()),
                ENT_NOQUOTES | ENT_HTML401 | ENT_SUBSTITUTE
            );
        } else {
            $type = get_class($error);
        }
        $message = (string)$error->getMessage();
        $title = $type;
        if ($message !== '') {
            $message = htmlspecialchars(
                $message, ENT_NOQUOTES | ENT_HTML401 | ENT_SUBSTITUTE
            );
            $title .= ': ' . $message;
        }
        Response::write(
            '<!DOCTYPE html><html><head><meta http-equiv="Content-Type"'
                . ' content="text/html;charset=utf-8"/><title>'
                . $title
                . '</title>'
        );
        $this->renderCss();
        Response::write('</head><body><table class="page-container"><tbody>');
        $this->renderContent($type, $message);
        Response::write('</tbody></table></body></html>');
    }

    /**
     * @param string $type
     * @param string $message
     * @return void
     */
    private function renderContent($type, $message) {
        Response::write(
            '<tr><td class="content-wrapper"><table class="content"><tbody>'
        );
        $this->renderErrorHeader($type, $message);
        Response::write('<tr><td class="file-wrapper">');
        $this->renderFile();
        Response::write('</td></tr>');
        if ($this->trace !== null && count($this->trace) > 0) {
            Response::write('<tr><td class="stack-trace-wrapper">');
            $this->renderStackTrace();
            Response::write('</td></tr>');
        }
        if (strlen($this->output) > 0) {
            Response::write('<tr><td class="output-wrapper">');
            $this->renderOutput();
            Response::write('</td></tr>');
        }
        Response::write('</tbody></table></td></tr>');
    }

    /**
     * @param string $type
     * @param string $message
     * @return void
     */
    private function renderErrorHeader($type, $message) {
        if ($this->error instanceof Error === false) {
            $type = str_replace('\\', '<span>\</span>', $type);
        }
        Response::write('<tr><td class="header"><h1>' . $type . '</h1>');
        $message = trim($message);
        if ($message !== '') {
            Response::write('<div class="message">' . $message . '</div>');
        }
        Response::write('</td></tr>');
    }

    /**
     * @return void
     */
    private function renderFile() {
        Response::write('<div class="file"><h2>File</h2>');
        $path = $this->error->getFile();
        $errorLineNumber = $this->error->getLine();
        $this->renderFileContent($path, $errorLineNumber);
        Response::write('</div>');
    }

    /**
     * @param string $path
     * @param int $errorLineNumber
     * @return void
     */
    private function renderFileContent($path, $errorLineNumber) {
        $this->renderPath(
            $path, ' <span class="line">' . $errorLineNumber . '</span>'
        );
        Response::write(
            '<div class="content"><table><tbody><tr>'
                . '<td class="index"><div class="index-content">'
        );
        $lines = $this->getLines($path, $errorLineNumber);
        foreach ($lines as $number => $line) {
            if ($number === $errorLineNumber) {
                Response::write(
                    '<div class="error-line-number"><div>'
                        . $number . '</div></div>'
                );
            } else {
                Response::write(
                    '<div class="line-number"><div>' . $number . '</div></div>'
                );
            }
        }
        Response::write("</div></td><td><pre>\n");
        foreach ($lines as $number => $line) {
            if ($number === $errorLineNumber) {
                Response::write(
                    '<span class="error-line">' . $line . "\n</span>"
                );
            } else {
                Response::write($line . "\n");
            }
        }
        Response::write('</pre></td></tr></tbody></table></div>');
    }

    /**
     * @return void
     */
    private function renderStackTrace() {
        Response::write(
            '<table class="stack-trace"><tr><td class="content">'
                . '<h2>Stack Trace</h2><table><tbody>'
        );
        $index = 0;
        $last = count($this->trace) - 1;
        foreach ($this->trace as $frame) {
            if ($frame !== '{main}') {
                $invocation = StackTraceFormatter::formatInvocation($frame);
                Response::write(
                    '<tr><td class="index">' . $index . '</td><td class="value'
                );
                if ($index === $last) {
                    Response::write(' last');
                }
                Response::write('"><div class="frame"><div class="position">');
                if (isset($frame['file'])) {
                    $this->renderPath(
                        $frame['file'],
                        ' <span class="line">' . $frame['line'] . '</span>'
                    );
                } else {
                    Response::write(
                        '<span class="internal">internal function</span>'
                    );
                }
                Response::write(
                    '</div><div class="invocation"><code>'
                        . $invocation
                        . '</code></div></div></td></tr>'
                );
            }
            ++$index;
        }
        Response::write('</tbody></table></td></tr></table>');
    }

    /**
     * @param string $path
     * @param int $errorLineNumber
     * @return array
     */
    private function getLines($path, $errorLineNumber) {
        $file = file_get_contents($path);
        $tokens = token_get_all($file);
        $firstLineNumber = 1;
        if ($errorLineNumber > 6) {
            $firstLineNumber = $errorLineNumber - 5;
        }
        $previousLineIndex = null;
        if ($firstLineNumber > 0) {
            foreach ($tokens as $index => $value) {
                if (is_string($value) === false) {
                    if ($value[2] < $firstLineNumber) {
                        $previousLineIndex = $index;
                    } else {
                        break;
                    }
                }
            }
        }
        $lineNumber = 0;
        $result = [];
        $buffer = '';
        foreach ($tokens as $index => $value) {
            if ($previousLineIndex !== null && $index < $previousLineIndex) {
                continue;
            }
            if (is_string($value)) {
                if ($value === '"') {
                    $buffer .= '<span class="string">' . $value . '</span>';
                } else {
                    $buffer .= '<span class="keyword">' . htmlspecialchars(
                        $value, ENT_NOQUOTES | ENT_HTML401 | ENT_SUBSTITUTE
                    ) . '</span>';
                }
                continue;
            }
            $lineNumber = $value[2];
            $type = $value[0];
            $content = str_replace(["\r\n", "\r"], "\n", $value[1]);
            $lines = explode("\n", $content);
            $lastLine = array_pop($lines);
            foreach ($lines as $line) {
                if ($lineNumber >= $firstLineNumber) {
                    $result[$lineNumber] =
                        $buffer . $this->formatToken($type, $line);
                    $buffer = '';
                }
                ++$lineNumber;
            }
            $buffer .= $this->formatToken($type, $lastLine);
            if ($lineNumber > $errorLineNumber + 5) {
                $buffer = false;
                break;
            }
        }
        if ($buffer !== false) {
            $result[$lineNumber] = $buffer;
        }
        if (isset($result[$errorLineNumber + 6])) {
            return array_slice(
                $result, 0, $errorLineNumber - $firstLineNumber + 6, true
            );
        }
        return $result;
    }

    /**
     * @param int $type
     * @param string $content
     * @return string
     */
    private function formatToken($type, $content) {
        $class = null;
        switch ($type) {
            case T_ENCAPSED_AND_WHITESPACE:
            case T_CONSTANT_ENCAPSED_STRING:
                $class = 'string';
                break;
            case T_WHITESPACE:
            case T_STRING:
            case T_NUM_STRING:
            case T_VARIABLE:
            case T_DNUMBER:
            case T_LNUMBER:
            case T_HALT_COMPILER:
            case T_EVAL:
            case T_CURLY_OPEN:
            case T_UNSET:
            case T_STRING_VARNAME:
            case T_PRINT:
            case T_REQUIRE:
            case T_REQUIRE_ONCE:
            case T_INCLUDE:
            case T_INCLUDE_ONCE:
            case T_ISSET:
            case T_LIST:
            case T_CLOSE_TAG:
            case T_OPEN_TAG:
            case T_OPEN_TAG_WITH_ECHO:
                break;
            case T_COMMENT:
            case T_DOC_COMMENT:
                $class = 'comment';
                break;
            case T_INLINE_HTML:
                $class = 'html';
                break;
            default:
                $class = 'keyword'; }
        $content = htmlspecialchars(
            $content, ENT_NOQUOTES | ENT_HTML401 | ENT_SUBSTITUTE
        );
        if ($class === null) {
            return $content;
        }
        return '<span class="' . $class . '">' . $content . '</span>';
    }

    /**
     * @param string $path
     * @param string $suffix
     * @return void
     */
    private function renderPath($path, $suffix = '') {
        Response::write(
            '<div class="path"><code>' . $path . '</code>' . $suffix . '</div>'
        );
    }

    private function renderOutput() {
        $length = strlen($this->output);
        Response::write(
            '<table class="output"><tbody><tr><td><h2>Output</h2>'
                . '<div class="size-wrapper"><div>Size: <span>'
                . $length
        );
        if ($length > 1) {
            Response::write(' bytes');
        } else {
            Response::write(' byte');
        }
        Response::write(
            '</span></div></div><div class="content"><pre>'
                . htmlspecialchars($this->output)
                . '</pre></div></td></tr></tbody></table>'
        );
    }

    /**
     * @return void
     */
    private function renderCss() {
        ob_start();
        ?><style>
        body {
            background: #fff;
            color: #333;
            font-family: Helvetica, Arial, sans-serif;
            font-size: 13px;
            -moz-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }
        table {
            font-size: 13px;<?php /* firebug preview */ ?>
            border-collapse: collapse;
        }
        td {
            padding: 0;
        }
        h1 {
            font-size: 21px;
            font-weight: normal;
            line-height: 25px;
            color: #e44;
            padding: 5px 10px 5px 10px;
        }
        h1 span {
            color: #bbb;
            padding: 0 5px;
        }
        pre, h1, h2, body {
            margin: 0;
        }
        h2 {
            font-size: 18px;
            font-family: "Times New Roman", Times, serif;
            padding: 0 10px;
        }
        .message, code, pre {
            font-family: Consolas, "Liberation Mono", Monospace, Menlo, Courier;
        }
        .page-container {
            width: 100%;
            min-width: 200px;
            _width: expression(
                (document.documentElement.clientWidth || document.body.clientWidth)
                    < 200 ? "200px" : ""
            );
        }
        .header {
            padding-bottom: 10px;
        }
        .message {
            font-size: 14px;
            padding: 2px 10px 5px 10px;
            line-height: 20px;
        }
        .content {
            width: 100%;
        }
        .stack-trace-wrapper, .output-wrapper {
            border: 1px solid #ccc;
        }
        .path {
            word-break: break-all;<?php /* ie */ ?>
            word-wrap: break-word;
        }
        .file .content {
            padding: 10px 0;
            background: #fff;
            border: 1px solid #e1e1e1;
        }
        .file-wrapper {
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f8f8f8;
        }
        .file h2 {
            padding-left: 0;
        }
        .file .path {
            padding: 5px 5px 8px 0;
        }
        .file table {
            width: 100%;
            line-height: 18px;
        }
        .file pre {
            font-size: 13px;
            margin-right: 10px;
            color: #00b;
        }
        .file .index .index-content {
            padding: 0;
            margin-left: 10px;
        }
        .file .index {
            width: 1px;
            text-align: right;
        }
        .file .index div {
            color: #aaa;
            padding: 0 5px;
            font-size: 12px;
        }
        .file .index .line-number {
            padding: 0 5px 0 0;
        }
        .file .line-number div {
            border-right: 1px solid #e1e1e1;
        }
        .file .index .error-line-number {
            padding: 0 5px 0 0;
            background: #ffa;
        }
        .file .index .error-line-number div {
            background-color: #d11;
            color: #fff;
            border-right: 1px solid #e1e1e1;
        }
        .file pre .keyword {
            color: #070;
        }
        .file pre .string {
            color: #d00;
        }
        .file pre .comment {
            color: #f80;
        }
        .file pre .html {
            color: #000;
        }
        .file .error-line {
            display: block;
            background: #ffa;
        }
        .stack-trace {
            width: 100%;
        }
        .content-wrapper, .stack-trace .content {
            padding: 10px;
        }
        .stack-trace h2 {
            padding: 0 0 10px 0;
        }
        .stack-trace table {
            width: 100%;
            border-radius: 2px;
            border-spacing: 0;<?php /* ie6 */ ?>
        }
        .stack-trace .path {
            color: #333;
        }
        .stack-trace .internal {
            color: #333;
            font-weight: bold;
        }
        .file .path .line, .stack-trace .line {
            font-size: 12px;
            color: #333;
            border-left: 1px solid #d5d5d5;
            padding-left: 8px;
            word-break: keep-all;
            white-space: nowrap;
        }
        .file .path code, .stack-trace .path code {
            padding-right: 3px;
        }
        .stack-trace table .last {
            border-bottom: 0;
        }
        .stack-trace .index {
            padding: 8px 5px 0 5px;
            width: 1px;
            color: #aaa;
            font-size:12px;
            border-right: 1px solid #e1e1e1;
            text-align: right;
            vertical-align: top;
        }
        .stack-trace .frame {
            background: #f8f8f8;
            padding: 7px 10px 10px;
            border-top: 1px solid #e1e1e1;
            border-right: 1px solid #e1e1e1;
        }
        .stack-trace .last .frame {
            border-bottom: 1px solid #e1e1e1;
        }
        .stack-trace .invocation {
            background: #fff;
            padding: 5px 10px;
            color: #777;
            margin-top: 7px;
            box-shadow: 0 1px 2px rgba(0,0,0,.1);
            border-left: 2px solid #e44;
        }
        .stack-trace .invocation code {
            word-wrap: break-word;
            word-break: break-all;
        }
        .output {
            width: 100%;
            background: #fff;
        }
        .output h2 {
            padding: 0;
            float: left;
        }
        .output td {
            padding: 10px;
            background: #f8f8f8;
        }
        .output .size-wrapper span {
            color: #333;
        }
        .output .size-wrapper {
            padding-bottom: 10px;
            padding-top: 0;
            line-height: 24px;
            float: right;
            color: #999;
        }
        .output .content {
            width: 100%;
            border: 1px solid #e1e1e1;
            background: #fff;
            clear: both;
        }
        .output .content pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            word-break: break-all;
            padding: 5px;
        }
        </style><?php
        $css = str_replace(
            ['    ', "\n", ' { ', ' } ', '; ', ': ', ', '],
            ['', ' ', '{', '}', ';', ':', ','],
            ob_get_contents()
        );
        ob_end_clean();
        Response::write($css);
    }
}
