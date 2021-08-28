<?php

declare(strict_types=1);


namespace Astaroth\CallBack;


use Astaroth\CallBack\Exceptions\WrongInputErrorException;
use Astaroth\CallBack\Input\DebugInput;
use Astaroth\CallBack\Input\NativeInput;
use Astaroth\Contracts\HandlerInterface;
use Exception;
use Astaroth\CallBack\Exceptions\SecurityErrorException;

final class Callback implements HandlerInterface
{
    private const OK = "ok";

    private object $data;
    /**
     * @var false
     */
    private bool $clearHeaders = true;

    private string $confirmation;
    private ?string $secret;
    private bool $handleRepeatedRequests;

    /**
     * @param string $confirmation
     * @param string|null $secret
     * @param bool $handleRepeatedRequests
     * @param string $input (php://input, debug....)
     * @throws Exception
     */
    public function __construct(
        string $confirmation,
        string $secret = null,
        bool   $handleRepeatedRequests = false,
        string $input = "php://input")
    {
        $this->confirmation = $confirmation;
        $this->secret = $secret;
        $this->handleRepeatedRequests = $handleRepeatedRequests;

        $this->getInput($input)->handleConfirmation();
    }

    /**
     * @param string $input
     * @return Callback
     * @throws Exception
     */
    private function getInput(string $input): Callback
    {
        $inputObject = match ($input) {
            NativeInput::INPUT => new NativeInput(),
            DebugInput::INPUT => new DebugInput(),
            //etc inputs...

            default => throw new WrongInputErrorException("$input - input is missing")
        };

        if ($inputObject->getData() === null) {
            self::echoAndDie();
        }

        $this->data = $inputObject->getData();
        return $this;
    }

    private function handleRepeatedRequests(): bool
    {
        if ($this->handleRepeatedRequests === false && isset(getallheaders()["X-Retry-Counter"])) {
            self::echoAndDie();
        }

        return false;
    }

    private function handleHeader(): void
    {
        if ($this->handleRepeatedRequests() === false) {
            $this->getClearHeaders() ? $this->clearHeadersAndSendOk() : self::echoOk();
        }
    }

    private static function echoOk(): void
    {
        echo self::OK;
    }

    private static function echoAndDie(): void
    {
        self::echoOk();
        die;
    }

    /**
     * @return false
     */
    public function getClearHeaders(): bool
    {
        return $this->clearHeaders;
    }

    /**
     * For debug
     * @return Callback
     */
    public function disableClearHeaders(): Callback
    {
        $this->clearHeaders = false;
        return $this;
    }

    private function clearHeadersAndSendOk(): void
    {
        set_time_limit(0);
        ini_set("display_errors", "Off");
        if (ob_get_contents()) {
            ob_end_clean();
        }

        //for nginx
        if (is_callable("fastcgi_finish_request")) {
            self::echoOk();
            session_write_close();
            fastcgi_finish_request();
        }

        //for apache
        ignore_user_abort(true);

        ob_start();
        header("Content-Encoding: none");
        header("Content-Length: 2");
        header("Connection: close");
        self::echoOk();
        ob_end_flush();
        flush();
    }

    /**
     * @throws Exception
     */
    private function handleConfirmation(): void
    {
        if (isset($this->data->type) && $this->data->type === "confirmation") {
            die($this->confirmation);
        }

        if ($this->secret !== null) {
            $secret = $this->data->secret ?? "nothing";
            if ($secret !== $this->secret) {
                throw new SecurityErrorException(
                    sprintf("WrongInputErrorException secret key\nThe key that should be: %s\nKey that came from VK: %s", $this->secret, $secret));
            }
        }
    }

    /**
     * @return object
     */
    private function getData(): object
    {
        return $this->data;
    }

    public function listen(callable $func): void
    {
        $this->handleHeader();
        $func($this->getData());
    }
}

$callback = new Callback("");
$callback->listen(function ($data) {
    //...
});