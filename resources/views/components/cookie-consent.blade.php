<div x-data="cookieConsent()" 
     x-show="showBanner" 
     x-cloak
     style="position: fixed; bottom: 16px; right: 16px; z-index: 9999; width: 600px; max-height: 600px;"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-4">
    
    <div class="bg-white rounded-2xl shadow-2xl overflow-y-auto" style="max-height: 600px;">
        
        <!-- Header -->
        <div class="flex items-center justify-between p-5">
            <h2 class="text-lg font-bold text-blue-600 bg-yellow-300 px-4 py-2 rounded-lg">จัดการ การอนุญาตใช้งาน Cookies</h2>
            <button @click="showBanner = false" class="text-gray-600 hover:text-gray-800 transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="p-5 space-y-4">
            <p class="text-sm text-gray-600 leading-relaxed">
                เว็บไซต์ {{ config('app.name') }} มีการใช้งานเทคโนโลยีคุกกี้ หรือ เทคโนโลยีอื่นที่มีลักษณะคล้ายกับคุกกี้ บนเว็บไซต์ของเรา 
                โปรดศึกษา นโยบายการใช้คุกกี้ และ นโยบายความเป็นส่วนตัวของข้อมูล ก่อนใช้บริการเว็บไซต์ ได้ที่ลิงค์ด้านล่าง
            </p>

            <!-- Cookie Options -->
            <div class="space-y-4 border-t border-gray-100 pt-4">
                <!-- Necessary Cookies -->
                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <h3 class="text-base text-blue-500 font-medium">คุกกี้ที่จำเป็น</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-orange-500 font-medium">Always active</span>
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <!-- Analytics Cookies -->
                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <h3 class="text-base text-blue-500 font-medium">คุกกี้เก็บสถิติ</h3>
                    <div class="flex items-center gap-2">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="analytics" class="sr-only peer">
                            <div class="w-12 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-400"></div>
                        </label>
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <!-- Marketing Cookies -->
                <div class="flex items-center justify-between py-3">
                    <h3 class="text-base text-blue-500 font-medium">คุกกี้ทางการตลาดออนไลน์</h3>
                    <div class="flex items-center gap-2">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="marketing" class="sr-only peer">
                            <div class="w-12 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-400"></div>
                        </label>
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="p-5 space-y-4">
            <div class="flex gap-3">
                <button @click="acceptAll()" 
                        class="flex-1 bg-blue-500 text-white font-medium py-3 px-4 rounded-xl hover:bg-blue-600 transition-all shadow-md">
                    ยอมรับ
                </button>
                <button @click="rejectAll()" 
                        class="flex-1 bg-white text-gray-700 font-medium py-3 px-4 rounded-xl hover:bg-gray-50 transition-colors border border-gray-200">
                    ปฏิเสธ
                </button>
                <button @click="savePreferences()" 
                        class="flex-1 bg-white text-gray-700 font-medium py-3 px-4 rounded-xl hover:bg-gray-50 transition-colors border border-gray-200">
                    จัดเก็บรายละเอียด
                </button>
            </div>

            <!-- Links -->
            <div class="flex justify-center gap-4 text-sm pt-2">
                <a href="#" class="text-blue-500 hover:text-blue-700 hover:underline">นโยบายการใช้คุกกี้</a>
                <a href="#" class="text-blue-500 hover:text-blue-700 hover:underline">นโยบายความเป็นส่วนตัวของข้อมูล</a>
            </div>
        </div>
    </div>
</div>

<!-- Cookie Settings Button (Fixed) - Shows only when consent was given -->
<div x-data="{ hasConsent: false }" x-init="hasConsent = document.cookie.includes('cookie_consent')">
    <button x-show="hasConsent"
            @click="document.querySelector('[x-data=\'cookieConsent()\']').__x.$data.showBanner = true"
            class="fixed bottom-4 right-4 z-50 bg-gradient-to-r from-blue-500 to-blue-600 text-white p-3 rounded-full shadow-lg hover:shadow-xl transition-all hover:scale-110"
            title="จัดการ Cookies">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
    </button>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('cookieConsent', {
            showBanner: false
        });
    });

    function cookieConsent() {
        return {
            showBanner: false,
            analytics: false,
            marketing: false,

            init() {
                // Check if consent was already given
                const consent = this.getCookie('cookie_consent');
                if (!consent) {
                    // Show banner after a short delay
                    setTimeout(() => {
                        this.showBanner = true;
                    }, 1000);
                } else {
                    // Load saved preferences
                    const preferences = JSON.parse(consent);
                    this.analytics = preferences.analytics || false;
                    this.marketing = preferences.marketing || false;
                }
            },

            acceptAll() {
                this.analytics = true;
                this.marketing = true;
                this.savePreferences();
            },

            rejectAll() {
                this.analytics = false;
                this.marketing = false;
                this.savePreferences();
            },

            savePreferences() {
                const preferences = {
                    necessary: true, // Always true
                    analytics: this.analytics,
                    marketing: this.marketing,
                    timestamp: new Date().toISOString()
                };
                
                // Save to cookie for 1 year
                this.setCookie('cookie_consent', JSON.stringify(preferences), 365);
                this.showBanner = false;

                // Show success message using SweetAlert if available
                if (window.Toast) {
                    window.Toast.fire({
                        icon: 'success',
                        title: 'บันทึกการตั้งค่า Cookies เรียบร้อยแล้ว'
                    });
                }
            },

            setCookie(name, value, days) {
                let expires = "";
                if (days) {
                    const date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax";
            },

            getCookie(name) {
                const nameEQ = name + "=";
                const ca = document.cookie.split(';');
                for (let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
                }
                return null;
            }
        }
    }
</script>
