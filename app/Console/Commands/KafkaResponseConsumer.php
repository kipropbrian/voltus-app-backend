<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Contracts\ConsumerMessage;

class KafkaResponseConsumer extends Command
{
    protected $signature = 'kafka:consume-responses';
    protected $description = 'Consumes face search responses from Kafka.';

    public function handle()
    {
        $consumer = Kafka::consumer(['face_search_responses'])
            ->withHandler(function(ConsumerMessage $message) {
                $payload = $message->getBody();
                $correlationId = $payload['request_id'] ?? null;
                $results = $payload['matches'] ?? $payload['error'] ?? null;

                // Store the results in a cache associated with the correlation ID
                cache()->put($correlationId, $results, 120);

                $this->info("Received response for correlation ID: {$correlationId}");
            })
            ->build();
            
            $consumer->consume();
    }
}
