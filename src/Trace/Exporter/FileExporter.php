<?php
/**
 * Copyright 2017 OpenCensus Authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenCensus\Trace\Exporter;

use OpenCensus\Trace\Span;
use OpenCensus\Trace\Tracer\TracerInterface;

/**
 * This implementation of the ExporterInterface appends a JSON
 * representation of the trace to a file.
 *
 * Example:
 * ```
 * use OpenCensus\Trace\Exporter\FileExporter;
 * use OpenCensus\Trace\Tracer;
 *
 * $exporter = new FileExporter('/path/to/file.txt');
 * Tracer::begin($exporter);
 * ```
 */
class FileExporter implements ExporterInterface
{
    /**
     * @var string The path to the output file.
     */
    private $filename;

    /**
     * Create a new EchoExporter
     *
     * @param string $filename The path to the output file.
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Report the provided Trace to a backend.
     *
     * @param  TracerInterface $tracer
     * @return bool
     */
    public function report(TracerInterface $tracer)
    {
        $spans = $this->convertSpans($tracer);
        return file_put_contents($this->filename, json_encode($spans) . PHP_EOL, FILE_APPEND) !== false;
    }

    /**
     * Convert spans into array ready for serialization.
     *
     * @param TracerInterface $tracer
     * @return array Representation of the collected trace spans ready for serialization
     */
    public function convertSpans(TracerInterface $tracer)
    {
        $traceId = $tracer->spanContext()->traceId();

        return array_map(function (Span $span) use ($traceId) {
            return [
                'traceId' => $traceId,
                'name' => $span->name(),
                'spanId' => $span->spanId(),
                'parentSpanId' => $span->parentSpanId(),
                'stackTrace' => $span->stackTrace(),
                'startTime' => $span->startTime(),
                'endTime' => $span->endTime(),
                'status' => $span->status(),
                'attributes' => $span->attributes(),
                'timeEvents' => $span->timeEvents(),
                'links' => $span->links(),
                'sameProcessAsParentSpan' => $span->sameProcessAsParentSpan(),
            ];
        }, $tracer->spans());
    }
}
