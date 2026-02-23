<?php

use App\Services\WhatsApp\PhoneNormalizer;

it('normalizes WAHA BR phones by removing mobile 9', function () {
    expect(PhoneNormalizer::normalizeWahaBrPhone('67992998146'))
        ->toBe('556792998146');
});

it('normalizes WAHA BR phones from formatted inputs', function () {
    expect(PhoneNormalizer::normalizeWahaBrPhone('+55 (67) 99299-8146'))
        ->toBe('556792998146');

    expect(PhoneNormalizer::normalizeWahaBrPhone('5567992998146'))
        ->toBe('556792998146');

    expect(PhoneNormalizer::normalizeWahaBrPhone('556792998146'))
        ->toBe('556792998146');
});

it('fails for invalid WAHA BR phones', function () {
    expect(fn () => PhoneNormalizer::normalizeWahaBrPhone('123'))
        ->toThrow(InvalidArgumentException::class, 'Telefone inválido para WAHA');

    expect(fn () => PhoneNormalizer::normalizeWahaBrPhone('5567123456789'))
        ->toThrow(InvalidArgumentException::class, 'Telefone inválido para WAHA');
});
