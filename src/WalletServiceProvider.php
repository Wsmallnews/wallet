<?php

namespace Wsmallnews\Wallet;

use Filament\Support\Assets\Asset;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wsmallnews\Pay\Contracts\WalletOperator;
use Wsmallnews\Support\Features\Modules\Module;
use Wsmallnews\Support\Features\Modules\ModuleRegistry;
use Wsmallnews\Wallet\Commands\WalletInstallCommand;
use Wsmallnews\Wallet\Commands\WalletReconcileCommand;
use Wsmallnews\Wallet\Contracts\RateResolverInterface;
use Wsmallnews\Wallet\Services\PayWalletOperator;
use Wsmallnews\Wallet\Services\RateResolvers\DbRateResolver;
use Wsmallnews\Wallet\Support\Utils;

class WalletServiceProvider extends PackageServiceProvider
{
    public static string $name = 'sn-wallet';

    public static string $viewNamespace = 'sn-wallet';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands());

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
        ModuleRegistry::register(new Module(
            id: static::$name,
            namespace: 'Wsmallnews\Wallet',
            plugin: WalletPlugin::class,
        ));

        // 钱包管理器（容器单例，app('sn-wallet') / Wallet facade）
        $this->app->singleton('sn-wallet', fn ($app) => $app->make(WalletManager::class));

        // 市场汇率源（可替换：外部 API 接入方换绑定即可）
        $this->app->bind(RateResolverInterface::class, DbRateResolver::class);

        // 实现 pay 包的余额通道契约：绑定后 sn-pay.channels.money 即可启用
        $this->app->bind(WalletOperator::class, PayWalletOperator::class);
    }

    public function packageBooted(): void
    {
        // 注册模型别名
        Relation::enforceMorphMap([
            'sn_wallet' => Utils::getWalletModel(),
            'sn_wallet_type' => Utils::getWalletTypeModel(),
            'sn_wallet_transaction' => Utils::getWalletTransactionModel(),
            'sn_wallet_recharge' => Utils::getRechargeModel(),
        ]);

        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());
    }

    protected function getAssetPackageName(): ?string
    {
        return 'wsmallnews/wallet';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            WalletInstallCommand::class,
            WalletReconcileCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_sn_wallet_types_table',
            'create_sn_wallet_rates_table',
            'create_sn_wallets_table',
            'create_sn_wallet_transactions_table',
            'create_sn_wallet_market_rates_table',
            'create_sn_wallet_recharges_table',
        ];
    }
}
