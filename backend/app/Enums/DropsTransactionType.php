<?php

namespace App\Enums;

enum DropsTransactionType: string
{
    case Purchase  = 'purchase';
    case Gift      = 'gift';
    case Earn      = 'earn';
    case Spend     = 'spend';
    case Payout    = 'payout';
    case Fee       = 'fee';
    case Refund    = 'refund';
    case Escrow    = 'escrow';
    case Release   = 'release';
    case CircleSubscription = 'circle_subscription';
    case GatedRoomEntry     = 'gated_room_entry';
    case SkillDropPurchase  = 'skill_drop_purchase';
    case FlowMarketPayment  = 'flow_market_payment';
}
