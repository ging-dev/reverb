<?php

use Laravel\Reverb\Pusher\Event as PusherEvent;
use Laravel\Reverb\Tests\Connection;

beforeEach(function () {
    $this->connection = new Connection;
});

it('can send an acknowledgement', function () {
    PusherEvent::handle(
        $this->connection,
        'pusher:connection_established'
    );

    $this->connection->assertSent([
        'event' => 'pusher:connection_established',
        'data' => json_encode([
            'socket_id' => $this->connection->id(),
            'activity_timeout' => 30,
        ]),
    ]);
});

it('can subscribe to a channel', function () {
    PusherEvent::handle(
        $this->connection,
        'pusher:subscribe',
        ['channel' => 'test-channel']
    );

    $this->connection->assertSent([
        'event' => 'pusher_internal:subscription_succeeded',
        'channel' => 'test-channel',
    ]);
});

it('can unsubscribe from a channel', function () {
    PusherEvent::handle(
        $this->connection,
        'pusher:unsubscribe',
        ['channel' => 'test-channel']
    );

    $this->connection->assertNothingSent();
});

it('can respond to a ping', function () {
    PusherEvent::handle(
        $this->connection,
        'pusher:ping',
    );

    $this->connection->assertSent([
        'event' => 'pusher:pong',
    ]);
});

it('can correctly format a payload', function () {
    $payload = PusherEvent::formatPayload(
        'foo',
        ['bar' => 'baz'],
        'test-channel',
        'reverb:'
    );

    expect($payload)->toBe(json_encode([
        'event' => 'reverb:foo',
        'data' => json_encode(['bar' => 'baz']),
        'channel' => 'test-channel',
    ]));

    $payload = PusherEvent::formatPayload('foo');

    expect($payload)->toBe(json_encode([
        'event' => 'pusher:foo',
    ]));
});

it('can correctly format an internal payload', function () {
    $payload = PusherEvent::formatInternalPayload(
        'foo',
        ['bar' => 'baz'],
        'test-channel',
        'reverb:'
    );

    expect($payload)->toBe(json_encode([
        'event' => 'pusher_internal:foo',
        'data' => json_encode(['bar' => 'baz']),
        'channel' => 'test-channel',
    ]));

    $payload = PusherEvent::formatInternalPayload('foo');

    expect($payload)->toBe(json_encode([
        'event' => 'pusher_internal:foo',
    ]));
});