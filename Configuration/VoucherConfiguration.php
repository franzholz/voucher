<?php

declare(strict_types=1);

namespace JambageCom\Voucher\Configuration;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

final readonly class VoucherConfiguration
{
    private int $codeSize;

    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $config = $extensionConfiguration->get('voucher');
        $config = is_array($config) ? $config : [];

        $this->codeSize = isset($config['codeSize']) && (int)$config['codeSize'] > 0
            ? (int)$config['codeSize']
            : 32;
    }

    public function getCodeSize(): int
    {
        return $this->codeSize;
    }
}
