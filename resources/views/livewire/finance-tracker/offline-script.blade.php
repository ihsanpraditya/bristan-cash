{{-- Alpine.js Offline Queue Component Script --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('offlineQueueHandler', () => ({
            isOnline: navigator.onLine,
            offlineQueueCount: 0,

            async init() {
                window.addEventListener('online', () => {
                    this.isOnline = true;
                    this.syncNow();
                });

                window.addEventListener('offline', () => {
                    this.isOnline = false;
                });

                await this.refreshQueueCount();
            },

            async getQueue() {
                try {
                    const raw = localStorage.getItem('bristan_offline_queue');
                    return raw ? JSON.parse(raw) : [];
                } catch (e) {
                    return [];
                }
            },

            async setQueue(items) {
                localStorage.setItem('bristan_offline_queue', JSON.stringify(items));
                this.offlineQueueCount = items.length;
            },

            async refreshQueueCount() {
                const queue = await this.getQueue();
                this.offlineQueueCount = queue.length;
            },

            async syncNow() {
                if (!navigator.onLine) return;
                const queue = await this.getQueue();
                if (queue.length === 0) return;

                try {
                    // Call Livewire syncBatch
                    const count = await @this.syncBatch(queue);
                    if (count > 0) {
                        await this.setQueue([]);
                    }
                } catch (e) {
                    console.warn('Gagal sync otomatis:', e);
                }
            }
        }));
    });
</script>
