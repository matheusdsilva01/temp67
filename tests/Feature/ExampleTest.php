<?php

test('home page returns a successful response', function () {
    $this->get('/')->assertOk();
});
