<?php

test('home page returns a successful response', function (): void {
    $this->get('/')->assertOk();
});
