<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\StreamOutput;

class ArtisanController extends Controller
{
    /** @var callable|false|null */
    private $previousErrorHandler = false;

    /**
     * @return bool|mixed
     */
    public function errorHandler(int $errno, string $errstr, string $errfile, int $errline)
    {
        if ($errno === 2 && \preg_match('/putenv\(\)/i', $errstr)) {
            return true;
        }
        if ($this->previousErrorHandler !== null) {
            return \call_user_func($this->previousErrorHandler, $errno, $errstr, $errfile, $errline);
        }

        return false;
    }

    /**
     * @param string|array<mixed> $command
     * @param array<mixed>|null   $parameters
     */
    protected function callArtisan(string|array $command, ?array $parameters = [], bool $isCron = false): Response
    {
        if ($this->previousErrorHandler === false) {
            $this->previousErrorHandler = \set_error_handler([$this, 'errorHandler']);
        }
        if ($isCron) {
            \ignore_user_abort(true);
        }

        if (\is_string($command)) {
            $command = [[$command, $parameters]];
        }

        $outputText = [];
        foreach ($command as $cmd) {
            $parameters = $cmd[1] ?? [];

            $outputStream = \fopen('php://output', 'w');
            $output = new StreamOutput($outputStream);

            \ob_start();
            $textParams = $this->formatParameters($parameters);
            echo "Command: {$cmd[0]}{$textParams}\n---------\n";

            $exit = Artisan::call($cmd[0], $parameters, $output);
            echo 'Exit code: ' . \intval($exit);

            $outputText[] = \ob_get_clean();

            \fclose($outputStream);
        }

        $requestTime = \round(\microtime(true) - \LARAVEL_START, 8);
        $outputText = \implode("\n\n", $outputText) . " | Done in {$requestTime}s";

        if ($isCron) {
            Log::channel('cron')->debug($outputText);
            $outputText = 'OK';
        }

        // Is running as logged in user - return 404 to not save to history
        // For other services accessing via key param send 200
        return \response($outputText, (Auth::user() === null ? 200 : 404))
            ->header('Content-Type', 'text/plain');
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function formatParameters(array $parameters): ?string
    {
        $text = [];

        foreach ($parameters as $name => $value) {
            if (\preg_match('/secret|pass/', $name)) {
                continue;
            }
            $text[] = $name . ($value === true ? '' : " => {$value}");
        }

        return empty($text) ? null : ' - [ ' . \implode(', ', $text) . ' ]';
    }

    public function deploy(Request $request): Response
    {
        return $this->callArtisan('deploy', ['version' => $request->query('version')]);
    }

    public function migrationStatus(): Response
    {
        return $this->callArtisan('migrate:status');
    }

    public function down(): Response
    {
        return $this->callArtisan(
            'down',
            ['--render' => 'errors.503', '--retry' => 60, '--secret' => \config('app.artisan_key')],
        );
    }

    public function up(): Response
    {
        return $this->callArtisan('up');
    }

    ///////////////
    // Cron jobs //
    ///////////////

    public function queueWork(): Response
    {
        // To properly use this, https://github.com/orobogenius/sansdaemon must be installed
        return $this->callArtisan(
            'queue:work',
            [
                '--tries' => 5,
                '--delay' => 15,
                '--sansdaemon' => true,
                '--queue' => 'password,job,default',
            ],
            true,
        );
    }

    public function runFailedJobs(): Response
    {
        return $this->callArtisan('queue:retry', ['id' => 'all']);
    }
}
