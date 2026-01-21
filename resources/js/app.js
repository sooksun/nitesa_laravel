import './bootstrap';
import Swal from 'sweetalert2';

// Make Swal available globally
window.Swal = Swal;

// Configure default SweetAlert2 theme
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

window.Toast = Toast;

// Livewire event listeners for SweetAlert2
document.addEventListener('livewire:init', () => {
    // Success toast notification
    Livewire.on('swal:success', (data) => {
        Toast.fire({
            icon: 'success',
            title: data[0].title || 'สำเร็จ!',
            text: data[0].text || ''
        });
    });

    // Error toast notification
    Livewire.on('swal:error', (data) => {
        Toast.fire({
            icon: 'error',
            title: data[0].title || 'เกิดข้อผิดพลาด!',
            text: data[0].text || ''
        });
    });

    // Info alert
    Livewire.on('swal:info', (data) => {
        Swal.fire({
            icon: 'info',
            title: data[0].title || 'แจ้งเตือน',
            text: data[0].text || '',
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#3085d6'
        });
    });

    // Confirm dialog
    Livewire.on('swal:confirm', (data) => {
        Swal.fire({
            title: data[0].title || 'ยืนยันการดำเนินการ?',
            text: data[0].text || '',
            icon: data[0].icon || 'warning',
            showCancelButton: true,
            confirmButtonColor: data[0].confirmButtonColor || '#3085d6',
            cancelButtonColor: '#6b7280',
            confirmButtonText: data[0].confirmButtonText || 'ยืนยัน',
            cancelButtonText: data[0].cancelButtonText || 'ยกเลิก',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch(data[0].onConfirmed);
            }
        });
    });
});

// Alpine.js is included with Livewire v3, no need to import separately
