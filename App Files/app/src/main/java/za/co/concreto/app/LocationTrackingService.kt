package za.co.concreto.app

import android.app.Notification
import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.app.Service
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.content.pm.ServiceInfo
import android.location.Location
import android.os.Build
import android.os.IBinder
import android.os.Looper
import android.util.Log
import androidx.core.app.NotificationCompat
import androidx.core.content.ContextCompat
import com.google.android.gms.location.FusedLocationProviderClient
import com.google.android.gms.location.LocationCallback
import com.google.android.gms.location.LocationRequest
import com.google.android.gms.location.LocationResult
import com.google.android.gms.location.LocationServices
import com.google.android.gms.location.Priority
import java.io.OutputStreamWriter
import java.net.HttpURLConnection
import java.net.URL
import java.util.concurrent.Executors

class LocationTrackingService : Service() {

    companion object {
        private const val TAG = "LocationTrackingService"
        private const val CHANNEL_ID = "location_tracking"
        private const val NOTIFICATION_ID = 1001
        private const val BASE_URL = "https://concreto.co.za"
        private const val TRACKING_INTERVAL_MS = 10_000L // 10 seconds
    }

    private lateinit var fusedLocationClient: FusedLocationProviderClient
    private var locationCallback: LocationCallback? = null
    private val executor = Executors.newSingleThreadExecutor()
    private var lastSentTime = 0L
    private var lastSentLat = 0.0
    private var lastSentLng = 0.0

    override fun onBind(intent: Intent?): IBinder? = null

    override fun onCreate() {
        super.onCreate()
        fusedLocationClient = LocationServices.getFusedLocationProviderClient(this)
        createNotificationChannel()
    }

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        val notification = buildNotification("Tracking your location for deliveries")

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.UPSIDE_DOWN_CAKE) {
            startForeground(NOTIFICATION_ID, notification, ServiceInfo.FOREGROUND_SERVICE_TYPE_LOCATION)
        } else {
            startForeground(NOTIFICATION_ID, notification)
        }

        startLocationUpdates()
        return START_STICKY
    }

    override fun onDestroy() {
        stopLocationUpdates()
        executor.shutdown()
        super.onDestroy()
    }

    private fun startLocationUpdates() {
        if (ContextCompat.checkSelfPermission(
                this, android.Manifest.permission.ACCESS_FINE_LOCATION
            ) != PackageManager.PERMISSION_GRANTED
        ) {
            Log.w(TAG, "Location permission not granted, stopping service")
            stopSelf()
            return
        }

        val locationRequest = LocationRequest.Builder(Priority.PRIORITY_HIGH_ACCURACY, TRACKING_INTERVAL_MS)
            .setMinUpdateIntervalMillis(TRACKING_INTERVAL_MS / 2)
            .setMinUpdateDistanceMeters(5f)
            .build()

        locationCallback = object : LocationCallback() {
            override fun onLocationResult(result: LocationResult) {
                result.lastLocation?.let { location ->
                    handleLocationUpdate(location)
                }
            }
        }

        fusedLocationClient.requestLocationUpdates(
            locationRequest,
            locationCallback!!,
            Looper.getMainLooper()
        )
    }

    private fun stopLocationUpdates() {
        locationCallback?.let {
            fusedLocationClient.removeLocationUpdates(it)
        }
        locationCallback = null
    }

    private fun handleLocationUpdate(location: Location) {
        val now = System.currentTimeMillis()
        val speed = location.speed * 3.6f // m/s → km/h
        val isStationary = speed < 1.0f
        val minInterval = if (isStationary) 60_000L else 10_000L

        // Throttle: don't send too frequently
        if (now - lastSentTime < minInterval) return

        // Skip if position hasn't changed meaningfully (within ~10m)
        val distance = floatArrayOf(0f)
        if (lastSentLat != 0.0 && lastSentLng != 0.0) {
            Location.distanceBetween(lastSentLat, lastSentLng, location.latitude, location.longitude, distance)
            if (distance[0] < 5f && isStationary) return
        }

        lastSentTime = now
        lastSentLat = location.latitude
        lastSentLng = location.longitude

        // Send location to server in background thread
        executor.execute {
            sendLocationToServer(location)
        }

        // Update notification with current speed
        val speedText = if (speed > 1) "${speed.toInt()} km/h" else "Stationary"
        val nm = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
        nm.notify(NOTIFICATION_ID, buildNotification("Tracking active - $speedText"))
    }

    private fun sendLocationToServer(location: Location) {
        // Get auth cookie from CookieManager to authenticate the request
        val cookies = android.webkit.CookieManager.getInstance().getCookie(BASE_URL)
        if (cookies.isNullOrEmpty()) {
            Log.d(TAG, "No auth cookies available, skipping location send")
            return
        }

        try {
            val url = URL("$BASE_URL/driver/location")
            val conn = url.openConnection() as HttpURLConnection
            conn.requestMethod = "POST"
            conn.setRequestProperty("Content-Type", "application/json")
            conn.setRequestProperty("Accept", "application/json")
            conn.setRequestProperty("Cookie", cookies)

            // Get XSRF token from cookies for CSRF protection
            val xsrfToken = extractXsrfToken(cookies)
            if (xsrfToken != null) {
                conn.setRequestProperty("X-XSRF-TOKEN", xsrfToken)
            }

            conn.doOutput = true
            conn.connectTimeout = 10_000
            conn.readTimeout = 10_000

            val speed = location.speed * 3.6f // m/s → km/h
            val json = """
                {
                    "lat": ${location.latitude},
                    "lng": ${location.longitude},
                    "speed": ${speed},
                    "heading": ${location.bearing},
                    "accuracy": ${location.accuracy}
                }
            """.trimIndent()

            val writer = OutputStreamWriter(conn.outputStream)
            writer.write(json)
            writer.flush()
            writer.close()

            val responseCode = conn.responseCode
            if (responseCode in 200..299) {
                Log.d(TAG, "Location sent: ${location.latitude}, ${location.longitude}")
            } else {
                Log.w(TAG, "Location send failed: HTTP $responseCode")
            }

            conn.disconnect()
        } catch (e: Exception) {
            Log.e(TAG, "Error sending location", e)
        }
    }

    private fun extractXsrfToken(cookies: String): String? {
        return cookies.split(";")
            .map { it.trim() }
            .firstOrNull { it.startsWith("XSRF-TOKEN=") }
            ?.substringAfter("XSRF-TOKEN=")
            ?.let { java.net.URLDecoder.decode(it, "UTF-8") }
    }

    private fun createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                CHANNEL_ID,
                "Location Tracking",
                NotificationManager.IMPORTANCE_LOW
            ).apply {
                description = "Shows when Concreto is tracking your location for deliveries"
                setShowBadge(false)
            }
            val nm = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
            nm.createNotificationChannel(channel)
        }
    }

    private fun buildNotification(text: String): Notification {
        val intent = Intent(this, MainActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_SINGLE_TOP
        }
        val pendingIntent = PendingIntent.getActivity(
            this, 0, intent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )

        return NotificationCompat.Builder(this, CHANNEL_ID)
            .setContentTitle("Concreto Driver")
            .setContentText(text)
            .setSmallIcon(R.drawable.ic_notification)
            .setContentIntent(pendingIntent)
            .setOngoing(true)
            .setSilent(true)
            .build()
    }
}
