<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace PrestaShopBundle\Command;

use DateTimeImmutable;
use DateTimeInterface;
use Locale;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShopBundle\Console\PrestaShopApplication;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This command displays information about the current PrestaShop installation.
 * It extends the default Symfony about command to include project-specific details.
 */
#[AsCommand(name: 'about', description: 'Display information about the current project')]
class AboutCommand extends Command
{
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        parent::__construct();
        $this->configuration = $configuration;
    }

    private function getPrestaShopInfoRows(): array
    {
        $rows = [
            new TableSeparator(),
            ['<info>Prestashop</>'],
            new TableSeparator(),
            ['Version', _PS_VERSION_],
            ['Debug mode', _PS_MODE_DEV_ ? 'true' : 'false'],
            ['Cache', $this->configuration->get('PS_SMARTY_CACHE') ? 'true' : 'false'],
        ];

        return $rows;
    }

    protected function configure(): void
    {
        $this
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command displays information about the current Symfony project.

The <info>PHP</info> section displays important configuration that could affect your application. The values might
be different between web and CLI.
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var PrestaShopApplication $application */
        $application = $this->getApplication();

        /** @var KernelInterface $kernel */
        $kernel = $application->getKernel();

        if (method_exists($kernel, 'getBuildDir')) {
            $buildDir = $kernel->getBuildDir();
        } else {
            $buildDir = $kernel->getCacheDir();
        }

        $rows = [
            ['<info>Symfony</>'],
            new TableSeparator(),
            ['Version', Kernel::VERSION],
            ['Long-Term Support', 4 === Kernel::MINOR_VERSION ? 'Yes' : 'No'],
            ['End of maintenance', Kernel::END_OF_MAINTENANCE . (self::isExpired(Kernel::END_OF_MAINTENANCE) ? ' <error>Expired</>' : ' (<comment>' . self::daysBeforeExpiration(Kernel::END_OF_MAINTENANCE) . '</>)')],
            ['End of life', Kernel::END_OF_LIFE . (self::isExpired(Kernel::END_OF_LIFE) ? ' <error>Expired</>' : ' (<comment>' . self::daysBeforeExpiration(Kernel::END_OF_LIFE) . '</>)')], new TableSeparator(),
            ['<info>Kernel</>'],
            new TableSeparator(),
            ['Type', $kernel::class],
            ['Environment', $kernel->getEnvironment()],
            ['Debug', $kernel->isDebug() ? 'true' : 'false'],
            ['Charset', $kernel->getCharset()],
            ['Cache directory', self::formatPath($kernel->getCacheDir(), $kernel->getProjectDir()) . ' (<comment>' . self::formatFileSize($kernel->getCacheDir()) . '</>)'],
            ['Build directory', self::formatPath($buildDir, $kernel->getProjectDir()) . ' (<comment>' . self::formatFileSize($buildDir) . '</>)'],
            ['Log directory', self::formatPath($kernel->getLogDir(), $kernel->getProjectDir()) . ' (<comment>' . self::formatFileSize($kernel->getLogDir()) . '</>)'],
            new TableSeparator(),
            ['<info>PHP</>'],
            new TableSeparator(),
            ['Version', \PHP_VERSION],
            ['Architecture', (\PHP_INT_SIZE * 8) . ' bits'],
            ['Intl locale', class_exists(Locale::class, false) && Locale::getDefault() ? Locale::getDefault() : 'n/a'],
            ['Timezone', date_default_timezone_get() . ' (<comment>' . (new DateTimeImmutable())->format(DateTimeInterface::W3C) . '</>)'], ['OPcache', \extension_loaded('Zend OPcache') && filter_var(\ini_get('opcache.enable'), \FILTER_VALIDATE_BOOL) ? 'true' : 'false'],
            ['APCu', \extension_loaded('apcu') && filter_var(\ini_get('apc.enabled'), \FILTER_VALIDATE_BOOL) ? 'true' : 'false'],
            ['Xdebug', \extension_loaded('xdebug') ? 'true' : 'false'],
        ];

        $rows = array_merge($rows, $this->getPrestaShopInfoRows());

        $io->table([], $rows);

        return 0;
    }

    private static function formatPath(string $path, string $baseDir): string
    {
        return preg_replace('~^' . preg_quote($baseDir, '~') . '~', '.', $path);
    }

    private static function formatFileSize(string $path): string
    {
        if (is_file($path)) {
            $size = filesize($path) ?: 0;
        } else {
            if (!is_dir($path)) {
                return 'n/a';
            }

            $size = 0;
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS)) as $file) {
                if ($file->isReadable()) {
                    $size += $file->getSize();
                }
            }
        }

        return Helper::formatMemory($size);
    }

    private static function isExpired(string $date): bool
    {
        $date = DateTimeImmutable::createFromFormat('d/m/Y', '01/' . $date);

        return false !== $date && new DateTimeImmutable() > $date->modify('last day of this month 23:59:59');
    }

    private static function daysBeforeExpiration(string $date): string
    {
        $date = DateTimeImmutable::createFromFormat('d/m/Y', '01/' . $date);

        return (new DateTimeImmutable())->diff($date->modify('last day of this month 23:59:59'))->format('in %R%a days');
    }
}
