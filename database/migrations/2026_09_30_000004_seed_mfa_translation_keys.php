<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedMfaTranslationKeys extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ltm_translations')) {
            return;
        }

        $translations = [
            'en' => [
                'mfa_security_title' => 'Account security',
                'mfa_setup_title' => 'Set up multi-factor authentication',
                'mfa_scan_prompt' => 'Scan this QR code with your authenticator app, then enter the code.',
                'mfa_manual_entry' => 'If your app cannot scan the QR code, use this setup URI.',
                'mfa_qr_alt' => 'Authenticator QR code',
                'mfa_enabled_notice' => 'Multi-factor authentication is enabled for your account.',
                'mfa_status' => 'Status',
                'mfa_regenerate_recovery_codes' => 'Regenerate recovery codes',
                'mfa_challenge_title' => 'Multi-factor authentication required',
                'mfa_challenge_prompt' => 'Enter the code from your authenticator app.',
                'mfa_recovery_prompt' => 'Enter one of your saved recovery codes.',
                'mfa_use_authenticator' => 'Use an authenticator code',
                'mfa_code' => 'Authentication code',
                'mfa_recovery_code' => 'Recovery code',
                'mfa_verify' => 'Verify',
                'mfa_invalid_code' => 'The authentication code is invalid or expired.',
                'mfa_use_recovery' => 'Use a recovery code',
                'mfa_sign_in_another_account' => 'Sign in with another account',
                'mfa_recovery_codes' => 'Recovery codes',
                'mfa_recovery_codes_notice' => 'Store these codes securely. They will only be shown once.',
                'mfa_disable_title' => 'Disable multi-factor authentication',
                'mfa_disable_prompt' => 'This removes the authenticator requirement from your account. You can turn it on again later.',
                'mfa_enable_mfa' => 'Enable MFA',
                'mfa_close' => 'Close',
                'mfa_disable' => 'Disable MFA',
                'mfa_cancel' => 'Cancel',
                'mfa_back_to_security' => 'Back to account security',
                'mfa_disabled' => 'Multi-factor authentication has been disabled.',
            ],
            'ar' => [
                'mfa_security_title' => 'أمان الحساب',
                'mfa_setup_title' => 'إعداد المصادقة متعددة العوامل',
                'mfa_scan_prompt' => 'امسح رمز QR باستخدام تطبيق المصادقة، ثم أدخل الرمز.',
                'mfa_manual_entry' => 'إذا تعذر على التطبيق مسح رمز QR، استخدم رابط الإعداد هذا.',
                'mfa_qr_alt' => 'رمز QR لتطبيق المصادقة',
                'mfa_enabled_notice' => 'المصادقة متعددة العوامل مفعلة لحسابك.',
                'mfa_status' => 'الحالة',
                'mfa_regenerate_recovery_codes' => 'إعادة إنشاء رموز الاسترداد',
                'mfa_challenge_title' => 'المصادقة متعددة العوامل مطلوبة',
                'mfa_challenge_prompt' => 'أدخل الرمز من تطبيق المصادقة.',
                'mfa_recovery_prompt' => 'أدخل أحد رموز الاسترداد المحفوظة.',
                'mfa_use_authenticator' => 'استخدام رمز المصادقة',
                'mfa_code' => 'رمز المصادقة',
                'mfa_recovery_code' => 'رمز الاسترداد',
                'mfa_verify' => 'تحقق',
                'mfa_invalid_code' => 'رمز المصادقة غير صحيح أو منتهي الصلاحية.',
                'mfa_use_recovery' => 'استخدام رمز الاسترداد',
                'mfa_sign_in_another_account' => 'تسجيل الدخول بحساب آخر',
                'mfa_recovery_codes' => 'رموز الاسترداد',
                'mfa_recovery_codes_notice' => 'احفظ هذه الرموز في مكان آمن. لن تظهر مرة أخرى.',
                'mfa_disable_title' => 'تعطيل المصادقة متعددة العوامل',
                'mfa_disable_prompt' => 'سيؤدي هذا إلى إزالة مطلب تطبيق المصادقة من حسابك. يمكنك تفعيله مرة أخرى لاحقاً.',
                'mfa_enable_mfa' => 'تفعيل المصادقة',
                'mfa_close' => 'إغلاق',
                'mfa_disable' => 'تعطيل المصادقة',
                'mfa_cancel' => 'إلغاء',
                'mfa_back_to_security' => 'العودة إلى أمان الحساب',
                'mfa_disabled' => 'تم تعطيل المصادقة متعددة العوامل.',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                DB::table('ltm_translations')->updateOrInsert(
                    ['locale' => $locale, 'group' => 'messages', 'key' => $key],
                    ['value' => $value, 'status' => 0, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('ltm_translations')) {
            return;
        }

        DB::table('ltm_translations')
            ->where('group', 'messages')
            ->whereIn('key', [
                'mfa_security_title',
                'mfa_setup_title',
                'mfa_scan_prompt',
                'mfa_manual_entry',
                'mfa_qr_alt',
                'mfa_enabled_notice',
                'mfa_status',
                'mfa_regenerate_recovery_codes',
                'mfa_challenge_title',
                'mfa_challenge_prompt',
                'mfa_recovery_prompt',
                'mfa_use_authenticator',
                'mfa_code',
                'mfa_recovery_code',
                'mfa_verify',
                'mfa_invalid_code',
                'mfa_use_recovery',
                'mfa_sign_in_another_account',
                'mfa_recovery_codes',
                'mfa_recovery_codes_notice',
                'mfa_disable_title',
                'mfa_disable_prompt',
                'mfa_enable_mfa',
                'mfa_close',
                'mfa_disable',
                'mfa_cancel',
                'mfa_back_to_security',
                'mfa_disabled',
            ])
            ->delete();
    }
}
