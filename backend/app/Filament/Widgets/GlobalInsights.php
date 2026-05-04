<?php

namespace App\Filament\Widgets;

use App\Enums\DropsTransactionType;
use App\Enums\GatedRoomStatus;
use App\Models\Circle;
use App\Models\DropsLedger;
use App\Models\GatedRoom;
use App\Models\User;
use App\Models\Wave;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GlobalInsights extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Registered accounts')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Total Waves', Wave::count())
                ->description('Short videos shared')
                ->descriptionIcon('heroicon-m-video-camera')
                ->color('success'),

            Stat::make('Total Circles', Circle::count())
                ->description('Communities created')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            Stat::make('Drops in Circulation', DropsLedger::where('direction', 'credit')->sum('amount') - DropsLedger::where('direction', 'debit')->sum('amount'))
                ->description('Total platform liquidity')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Active Gated Rooms', GatedRoom::where('status', GatedRoomStatus::Live)->count())
                ->description('Live sessions right now')
                ->descriptionIcon('heroicon-m-microphone')
                ->color('danger'),

            Stat::make('Platform Revenue', DropsLedger::where('type', DropsTransactionType::Fee)->sum('amount'))
                ->description('Total fees collected')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}
