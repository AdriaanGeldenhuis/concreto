package za.co.concreto.app

import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.os.Build
import android.util.Log
import androidx.core.app.NotificationCompat
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage
import java.io.OutputStreamWriter
import java.net.HttpURLConnection
import java.net.URL
import java.util.concurrent.Executors

class MyFirebaseMessagingService : FirebaseMessagingService() {

    companion object {
        private const val TAG = "MyFirebaseMsgService"
        private const val BASE_URL = "https://concreto.co.za"
        private const val CHANNEL_ID = "concreto_notifications"
        private var notificationIdCounter = 100
    }

    private val executor = Executors.newSingleThreadExecutor()

    override fun onMessageReceived(remoteMessage: RemoteMessage) {
        super.onMessageReceived(remoteMessage)

        Log.d(TAG, "From: ${remoteMessage.from}")

        val title = remoteMessage.notification?.title
            ?: remoteMessage.data["title"]
            ?: "Concreto"
        val body = remoteMessage.notification?.body
            ?: remoteMessage.data["body"]
            ?: ""

        if (body.isNotEmpty()) {
            sendNotification(title, body)
        }
    }

    override fun onNewToken(token: String) {
        super.onNewToken(token)
        Log.d(TAG, "Refreshed token: $token")
        sendRegistrationToServer(token)
    }

    private fun sendRegistrationToServer(token: String?) {
        if (token.isNullOrEmpty()) return

        executor.execute {
            val cookies = android.webkit.CookieManager.getInstance().getCookie(BASE_URL)
            if (cookies.isNullOrEmpty()) {
                Log.d(TAG, "No auth cookies, cannot register FCM token yet")
                return@execute
            }

            try {
                val url = URL("$BASE_URL/api/device-tokens")
                val conn = url.openConnection() as HttpURLConnection
                conn.requestMethod = "POST"
                conn.setRequestProperty("Content-Type", "application/json")
                conn.setRequestProperty("Accept", "application/json")
                conn.setRequestProperty("Cookie", cookies)

                val xsrfToken = cookies.split(";")
                    .map { it.trim() }
                    .firstOrNull { it.startsWith("XSRF-TOKEN=") }
                    ?.substringAfter("XSRF-TOKEN=")
                    ?.let { java.net.URLDecoder.decode(it, "UTF-8") }

                if (xsrfToken != null) {
                    conn.setRequestProperty("X-XSRF-TOKEN", xsrfToken)
                }

                conn.doOutput = true
                conn.connectTimeout = 10_000
                conn.readTimeout = 10_000

                val json = """{"token": "$token", "platform": "android"}"""
                val writer = OutputStreamWriter(conn.outputStream)
                writer.write(json)
                writer.flush()
                writer.close()

                val responseCode = conn.responseCode
                if (responseCode in 200..299) {
                    Log.d(TAG, "FCM token registered with server")
                } else {
                    Log.w(TAG, "FCM token registration failed: HTTP $responseCode")
                }
                conn.disconnect()
            } catch (e: Exception) {
                Log.e(TAG, "Error registering FCM token", e)
            }
        }
    }

    private fun sendNotification(title: String?, messageBody: String?) {
        createNotificationChannel()

        val intent = Intent(this, MainActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_SINGLE_TOP or Intent.FLAG_ACTIVITY_CLEAR_TOP
        }
        val pendingIntent = PendingIntent.getActivity(
            this, 0, intent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )

        val notificationBuilder = NotificationCompat.Builder(this, CHANNEL_ID)
            .setSmallIcon(R.drawable.ic_notification)
            .setContentTitle(title)
            .setContentText(messageBody)
            .setAutoCancel(true)
            .setContentIntent(pendingIntent)
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setStyle(NotificationCompat.BigTextStyle().bigText(messageBody))

        val notificationManager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
        notificationManager.notify(notificationIdCounter++, notificationBuilder.build())
    }

    private fun createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                CHANNEL_ID,
                "Concreto Notifications",
                NotificationManager.IMPORTANCE_HIGH
            ).apply {
                description = "Order updates and delivery notifications"
            }
            val nm = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
            nm.createNotificationChannel(channel)
        }
    }
}
