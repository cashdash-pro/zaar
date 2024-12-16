<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta name="shopify-api-key" content="{{config('zaar.shopify_app.client_id')}}"/>
        <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>
	</head>
    <body class="font-sans antialiased">
    <script>
        // Shopify's redirect is broken atm on firefox
        const handleRedirect = async () => {
            try {
                const token = await shopify.idToken();
                const redirectUrl = new URLSearchParams(window.location.search).get('redirect_url');

                // add the token to the redirect URL
                if (redirectUrl) {

                    @if(!app()->isProduction())
                    shopify.toast.show('A hard navigation was recovered, ensure this is expected (this message is only shown in dev mode)', {
                        isError: true
                    })
                    @endif

                    const url = new URL(redirectUrl);
                    const shop = shopify.config.shop;
                    url.searchParams.append('id_token', token);
                    url.searchParams.append('shop', shop);
                    window.location.href = url.toString();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        };

        document.addEventListener('DOMContentLoaded', handleRedirect);
    </script>
    </body>
</html>
