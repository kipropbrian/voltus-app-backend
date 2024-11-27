<?php

namespace App\Jobs;

use App\FacePlusClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessFacePlusRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $options;
    protected $callback;

    /**
     * Create a new job instance.
     *
     * @param string $path
     * @param array $options
     * @param string|null $callback
     */
    public function __construct(string $path, array $options, string $callback = null)
    {
        $this->path = $path;
        $this->options = $options;
        $this->callback = $callback;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FacePlusClient $client)
    {
        $response = $client->request($this->path, $this->options);

        // If a callback event is specified, dispatch it with the response
        if ($this->callback) {
            event(new $this->callback($response));
        }

        return $response;
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        \Log::error('FacePlus API request failed', [
            'path' => $this->path,
            'options' => $this->options,
            'error' => $exception->getMessage()
        ]);
    }
}