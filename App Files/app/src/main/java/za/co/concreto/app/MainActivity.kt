package za.co.concreto.app

import android.Manifest
import android.content.Intent
import android.content.pm.PackageManager
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.view.View
import android.view.WindowManager
import android.webkit.CookieManager
import android.webkit.GeolocationPermissions
import android.webkit.ValueCallback
import android.webkit.WebChromeClient
import android.webkit.WebResourceRequest
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Scaffold
import androidx.compose.runtime.Composable
import androidx.compose.runtime.remember
import androidx.compose.ui.Modifier
import androidx.compose.ui.viewinterop.AndroidView
import androidx.core.content.ContextCompat
import za.co.concreto.app.ui.theme.ConcretoTheme

class MainActivity : ComponentActivity() {

    private var webView: WebView? = null
    private var geolocationCallback: GeolocationPermissions.Callback? = null
    private var geolocationOrigin: String? = null

    private val locationPermissionRequest = registerForActivityResult(
        ActivityResultContracts.RequestMultiplePermissions()
    ) { permissions ->
        val fineGranted = permissions[Manifest.permission.ACCESS_FINE_LOCATION] == true
        val coarseGranted = permissions[Manifest.permission.ACCESS_COARSE_LOCATION] == true

        // Grant or deny the WebView geolocation callback
        geolocationCallback?.invoke(geolocationOrigin, fineGranted || coarseGranted, false)
        geolocationCallback = null
        geolocationOrigin = null

        // Start the background location service if permission granted
        if (fineGranted || coarseGranted) {
            startLocationService()
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        // Keep screen on while the driver app is active
        window.addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)

        enableEdgeToEdge()

        // Request location permissions upfront
        requestLocationPermissions()

        setContent {
            ConcretoTheme {
                Scaffold { innerPadding ->
                    WebViewScreen(
                        url = "https://concreto.co.za/driver",
                        modifier = Modifier.padding(innerPadding),
                        onWebViewCreated = { wv -> webView = wv },
                        onGeolocationRequest = { origin, callback ->
                            handleGeolocationRequest(origin, callback)
                        }
                    )
                }
            }
        }
    }

    override fun onResume() {
        super.onResume()
        webView?.onResume()
        startLocationService()
    }

    override fun onPause() {
        webView?.onPause()
        super.onPause()
    }

    override fun onDestroy() {
        webView?.destroy()
        super.onDestroy()
    }

    private fun requestLocationPermissions() {
        val permissions = mutableListOf(
            Manifest.permission.ACCESS_FINE_LOCATION,
            Manifest.permission.ACCESS_COARSE_LOCATION,
        )
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            permissions.add(Manifest.permission.POST_NOTIFICATIONS)
        }
        locationPermissionRequest.launch(permissions.toTypedArray())
    }

    private fun handleGeolocationRequest(
        origin: String,
        callback: GeolocationPermissions.Callback
    ) {
        if (ContextCompat.checkSelfPermission(
                this, Manifest.permission.ACCESS_FINE_LOCATION
            ) == PackageManager.PERMISSION_GRANTED
        ) {
            callback.invoke(origin, true, false)
        } else {
            // Store callback and request permission
            geolocationCallback = callback
            geolocationOrigin = origin
            locationPermissionRequest.launch(
                arrayOf(
                    Manifest.permission.ACCESS_FINE_LOCATION,
                    Manifest.permission.ACCESS_COARSE_LOCATION,
                )
            )
        }
    }

    private fun startLocationService() {
        if (ContextCompat.checkSelfPermission(
                this, Manifest.permission.ACCESS_FINE_LOCATION
            ) == PackageManager.PERMISSION_GRANTED
        ) {
            val intent = Intent(this, LocationTrackingService::class.java)
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                startForegroundService(intent)
            } else {
                startService(intent)
            }
        }
    }
}

@Composable
fun WebViewScreen(
    url: String,
    modifier: Modifier = Modifier,
    onWebViewCreated: (WebView) -> Unit = {},
    onGeolocationRequest: (String, GeolocationPermissions.Callback) -> Unit = { _, _ -> },
) {
    AndroidView(
        modifier = modifier.fillMaxSize(),
        factory = { context ->
            WebView(context).apply {
                // Enable cookies for session auth
                CookieManager.getInstance().setAcceptCookie(true)
                CookieManager.getInstance().setAcceptThirdPartyCookies(this, true)

                settings.apply {
                    javaScriptEnabled = true
                    domStorageEnabled = true
                    databaseEnabled = true
                    setGeolocationEnabled(true)
                    cacheMode = WebSettings.LOAD_DEFAULT
                    mixedContentMode = WebSettings.MIXED_CONTENT_NEVER_ALLOW
                    userAgentString = "$userAgentString ConcretoApp/1.0"
                    mediaPlaybackRequiresUserGesture = false
                    allowFileAccess = true
                }

                // Handle navigation within the app
                webViewClient = object : WebViewClient() {
                    override fun shouldOverrideUrlLoading(
                        view: WebView?,
                        request: WebResourceRequest?
                    ): Boolean {
                        val host = request?.url?.host ?: return false
                        // Keep concreto.co.za navigation inside the WebView
                        if (host.contains("concreto.co.za")) {
                            return false
                        }
                        // Open Google Maps and external links in the browser
                        val intent = Intent(Intent.ACTION_VIEW, request.url)
                        context.startActivity(intent)
                        return true
                    }
                }

                // Handle geolocation permission requests from JavaScript
                webChromeClient = object : WebChromeClient() {
                    override fun onGeolocationPermissionsShowPrompt(
                        origin: String,
                        callback: GeolocationPermissions.Callback
                    ) {
                        onGeolocationRequest(origin, callback)
                    }
                }

                scrollBarStyle = View.SCROLLBARS_INSIDE_OVERLAY
                loadUrl(url)
                onWebViewCreated(this)
            }
        }
    )
}
