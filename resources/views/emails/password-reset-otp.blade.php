<h2>รีเซ็ตรหัสผ่าน FixIT</h2>
<p>สวัสดี {{ $user->name }}</p>
<p>รหัส OTP สำหรับตั้งรหัสผ่านใหม่ของคุณคือ:</p>
<p style="font-size: 28px; font-weight: 700; letter-spacing: 8px;">{{ $otp }}</p>
<p>รหัสนี้ใช้ได้ถึงเวลา {{ $expiresAt->timezone('Asia/Bangkok')->format('H:i') }} น. และใช้ได้เพียงครั้งเดียว</p>
<p>หากคุณไม่ได้เป็นผู้ขอรีเซ็ตรหัสผ่าน ให้ละเว้นอีเมลฉบับนี้</p>
