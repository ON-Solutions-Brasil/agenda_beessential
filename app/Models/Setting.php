<?php

namespace App\Models;

use App\Core\Model;

class Setting extends Model
{
    protected string $table = 'settings';

    /**
     * Busca o valor de uma configuração pelo key_name.
     */
    public function getValue(string $key, $default = null): mixed
    {
        $setting = $this->findBy('key_name', $key);
        if (!$setting) {
            return $default;
        }

        return $this->castValue($setting->value, $setting->type);
    }

    /**
     * Define o valor de uma configuração.
     */
    public function setValue(string $key, mixed $value): bool
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        $setting = $this->findBy('key_name', $key);

        // Se a chave ainda não existe, cria (upsert) para não perder configuração.
        if (!$setting) {
            return $this->create([
                'key_name'   => $key,
                'value'      => (string) $value,
                'type'       => 'text',
                'group_name' => 'geral',
                'label'      => $key,
            ]) > 0;
        }

        return $this->update($setting->id, ['value' => (string) $value]);
    }

    /**
     * Retorna todas as configurações agrupadas.
     */
    public function allGrouped(): array
    {
        $settings = $this->all('group_name ASC, id ASC');
        $grouped = [];

        foreach ($settings as $setting) {
            $grouped[$setting->group_name][] = $setting;
        }

        return $grouped;
    }

    /**
     * Salva múltiplas configurações de uma vez.
     */
    public function saveMultiple(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->setValue($key, $value);
        }
    }

    /**
     * Converte o valor para o tipo correto.
     */
    private function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'number'  => (int) $value,
            'boolean' => (bool) $value,
            'json'    => json_decode($value, true),
            default   => $value,
        };
    }
}
