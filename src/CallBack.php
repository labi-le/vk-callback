<?php

declare(strict_types=1);


namespace Astaroth\CallBack;


use Exception;
use Astaroth\CallBack\Exceptions\SecurityErrorException;

class CallBack
{

    private object $data;

    public function __construct(public string $confirmation, public ?string $secret = null, public bool $handleRepeatedRequests = true, $input = 'php://input')
    {
        $this->getInput($input)->handleConfirmation();
    }


    public function __destruct()
    {
        $this->handleHeader();
    }

    /**
     * @param string|array $input
     * @return CallBack
     */
    private function getInput(string|array $input): static
    {
        if ($input === 'php://input') {
            $raw_data = file_get_contents($input);
            $this->data = json_decode($raw_data, false);

        }

        return $this;
    }

    private function handleRepeatedRequests(): bool
    {
        if ($this->handleRepeatedRequests === false && isset(getallheaders()['X-Retry-Counter'])) {
            die('ok');
        }

        return false;
    }

    private function handleHeader(): void
    {
        $this->handleRepeatedRequests() ?: $this->sendOK();
    }

    private function sendOK(): void
    {
        set_time_limit(0);
        ini_set('display_errors', 'Off');
        if (ob_get_contents()) {
            ob_end_clean();
        }

        //for nginx
        if (is_callable('fastcgi_finish_request')) {
            echo 'ok';
            session_write_close();
            fastcgi_finish_request();
        }

        //for apache
        ignore_user_abort(true);

        ob_start();
        header('Content-Encoding: none');
        header('Content-Length: 2');
        header('Connection: close');
        echo 'ok';
        ob_end_flush();
        flush();
    }

    /**
     * @throws Exception
     */
    private function handleConfirmation(): void
    {
        if (isset($this->data['type']) && $this->data['type'] === 'confirmation') {
            die($this->confirmation);
        }

        if ($this->secret !== null) {
            $secret = $this->data['secret'] ?? 'nothing';
            if ($secret !== $this->secret) {
                throw new SecurityErrorException(
                    sprintf("Wrong secret key\nThe key that should be: %s\nKey that came from VK: %s", $this->secret, $secret));
            }
        }
    }

    /**
     * @return object
     */
    public function getData(): object
    {
        return $this->data;
    }
}