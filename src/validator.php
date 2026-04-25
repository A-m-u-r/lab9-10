<?php
declare(strict_types=1);

final class Validator
{
    /** @var array<string, string[]> */
    public array $errors = [];
    /** @var array<string, mixed> */
    public array $clean = [];

    public function __construct(private readonly array $input) {}

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    private function add(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    private function raw(string $field): string
    {
        $v = $this->input[$field] ?? '';
        return is_string($v) ? trim($v) : '';
    }

    public function string(string $field, string $label, int $min, int $max, bool $required = true): self
    {
        $v = $this->raw($field);
        if ($v === '') {
            if ($required) $this->add($field, "Поле «{$label}» обязательно для заполнения.");
            return $this;
        }
        $len = mb_strlen($v, 'UTF-8');
        if ($len < $min) $this->add($field, "«{$label}» должно содержать минимум {$min} символов.");
        if ($len > $max) $this->add($field, "«{$label}» должно содержать не более {$max} символов.");
        if (!isset($this->errors[$field])) $this->clean[$field] = $v;
        return $this;
    }

    public function fullName(string $field, string $label): self
    {
        $v = $this->raw($field);
        if ($v === '') {
            $this->add($field, "Поле «{$label}» обязательно для заполнения.");
            return $this;
        }
        if (mb_strlen($v, 'UTF-8') < 2 || mb_strlen($v, 'UTF-8') > 100) {
            $this->add($field, "«{$label}» должно содержать от 2 до 100 символов.");
            return $this;
        }
        if (!preg_match('/^[\p{L}\s\-\']+$/u', $v)) {
            $this->add($field, "«{$label}» может содержать только буквы, пробелы и дефисы.");
            return $this;
        }
        $this->clean[$field] = $v;
        return $this;
    }

    public function email(string $field, string $label): self
    {
        $v = $this->raw($field);
        if ($v === '') {
            $this->add($field, "Поле «{$label}» обязательно для заполнения.");
            return $this;
        }
        if (mb_strlen($v, 'UTF-8') > 254) {
            $this->add($field, "«{$label}» слишком длинный.");
            return $this;
        }
        if (!filter_var($v, FILTER_VALIDATE_EMAIL)) {
            $this->add($field, "Поле «{$label}» содержит некорректный адрес.");
            return $this;
        }
        $this->clean[$field] = mb_strtolower($v, 'UTF-8');
        return $this;
    }

    public function password(string $field, string $label, int $min = 8, int $max = 72): self
    {
        $v = $this->input[$field] ?? '';
        if (!is_string($v) || $v === '') {
            $this->add($field, "Поле «{$label}» обязательно для заполнения.");
            return $this;
        }
        if (strlen($v) < $min) {
            $this->add($field, "«{$label}» должен быть не короче {$min} символов.");
            return $this;
        }
        if (strlen($v) > $max) {
            $this->add($field, "«{$label}» слишком длинный.");
            return $this;
        }
        if (!preg_match('/[A-Za-zА-Яа-яЁё]/u', $v) || !preg_match('/\d/', $v)) {
            $this->add($field, "«{$label}» должен содержать буквы и цифры.");
            return $this;
        }
        $this->clean[$field] = $v;
        return $this;
    }

    public function phone(string $field, string $label): self
    {
        $v = $this->raw($field);
        if ($v === '') {
            $this->add($field, "Поле «{$label}» обязательно для заполнения.");
            return $this;
        }
        $digits = preg_replace('/\D+/', '', $v) ?? '';
        if (strlen($digits) === 11 && $digits[0] === '8') {
            $digits = '7' . substr($digits, 1);
        }
        if (strlen($digits) !== 11 || $digits[0] !== '7') {
            $this->add($field, "«{$label}» должен быть в формате +7 XXX XXX-XX-XX.");
            return $this;
        }
        $this->clean[$field] = '+' . $digits;
        return $this;
    }

    public function in(string $field, string $label, array $allowed): self
    {
        $v = $this->raw($field);
        if ($v === '' || !in_array($v, $allowed, true)) {
            $this->add($field, "Поле «{$label}» содержит недопустимое значение.");
            return $this;
        }
        $this->clean[$field] = $v;
        return $this;
    }

    public function intRange(string $field, string $label, int $min, int $max): self
    {
        $raw = $this->raw($field);
        if ($raw === '') {
            $this->add($field, "Поле «{$label}» обязательно для заполнения.");
            return $this;
        }
        if (!preg_match('/^\d+$/', $raw)) {
            $this->add($field, "«{$label}» должно быть целым числом.");
            return $this;
        }
        $v = (int)$raw;
        if ($v < $min || $v > $max) {
            $this->add($field, "«{$label}» должно быть в диапазоне от {$min} до {$max}.");
            return $this;
        }
        $this->clean[$field] = $v;
        return $this;
    }

    public function priceRange(string $field, string $label, float $min, float $max): self
    {
        $raw = str_replace(',', '.', $this->raw($field));
        if ($raw === '') {
            $this->add($field, "Поле «{$label}» обязательно для заполнения.");
            return $this;
        }
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $raw)) {
            $this->add($field, "«{$label}» должно быть числом, например 1500 или 1500.50.");
            return $this;
        }
        $v = (float)$raw;
        if ($v < $min || $v > $max) {
            $this->add($field, "«{$label}» должно быть в диапазоне от {$min} до {$max}.");
            return $this;
        }
        $this->clean[$field] = $v;
        return $this;
    }

    public function dateNotPast(string $field, string $label): self
    {
        $v = $this->raw($field);
        if ($v === '') {
            $this->add($field, "Поле «{$label}» обязательно для заполнения.");
            return $this;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
            $this->add($field, "«{$label}» должно быть в формате ГГГГ-ММ-ДД.");
            return $this;
        }
        $d = DateTimeImmutable::createFromFormat('!Y-m-d', $v);
        if (!$d || $d->format('Y-m-d') !== $v) {
            $this->add($field, "«{$label}» содержит несуществующую дату.");
            return $this;
        }
        $today = new DateTimeImmutable('today');
        if ($d < $today) {
            $this->add($field, "«{$label}» не может быть в прошлом.");
            return $this;
        }
        $this->clean[$field] = $v;
        return $this;
    }
}
