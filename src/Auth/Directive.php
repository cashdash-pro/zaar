<?php

namespace CashDash\Zaar\Auth;

class Directive
{
    /**
     * Compiles the "@zaarHead" directive.
     *
     * @param  string  $expression
     */
    public static function compileHead($expression = ''): string
    {
        return '<?php if (\CashDash\Zaar\Zaar::sessionStarted() && \CashDash\Zaar\Zaar::isEmbedded()): ?>
            <meta name="shopify-api-key" content="<?php echo e(config(\'zaar.shopify_app.client_id\')); ?>"/>
            <meta name="shopify-shop" content="<?php echo e(\CashDash\Zaar\Zaar::session()->shop); ?>"/>
            <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>
        <?php endif; ?>';
    }
}
