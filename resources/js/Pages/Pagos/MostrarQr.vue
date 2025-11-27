<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    orden: Object,
    qrBase64: String,
    transactionId: String,
    paymentStatus: Number,
    expirationDate: String,
    testAmount: Number,
    realAmount: Number,
});

const verificando = ref(false);
const mensaje = ref('');
const mensajeTipo = ref(''); // 'success', 'error', 'info'
const autoVerificacion = ref(null);

const verificarPago = async () => {
    verificando.value = true;
    mensaje.value = '';

    try {
        const response = await fetch(route('ordenes.pago-qr.verificar', props.orden.nro), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });

        const data = await response.json();

        if (data.success) {
            mensajeTipo.value = 'success';
            mensaje.value = data.message;

            // Redirigir después de 2 segundos
            setTimeout(() => {
                router.visit(data.redirect);
            }, 2000);

            // Detener verificación automática
            if (autoVerificacion.value) {
                clearInterval(autoVerificacion.value);
            }
        } else {
            mensajeTipo.value = data.status === 1 ? 'info' : 'error';
            mensaje.value = data.message;
        }
    } catch (error) {
        mensajeTipo.value = 'error';
        mensaje.value = 'Error al verificar el pago. Intente nuevamente.';
    } finally {
        verificando.value = false;
    }
};

// Verificación automática cada 10 segundos
onMounted(() => {
    autoVerificacion.value = setInterval(() => {
        if (!verificando.value) {
            verificarPago();
        }
    }, 10000); // 10 segundos
});

onUnmounted(() => {
    if (autoVerificacion.value) {
        clearInterval(autoVerificacion.value);
    }
});

const volver = () => {
    router.visit(route('ordenes.show', props.orden.nro));
};
</script>

<template>
    <Head title="Pago con QR" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Encabezado -->
                        <div class="mb-6">
                            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                                💳 Pago con QR - Orden {{ orden.nro }}
                            </h1>
                            <p class="text-sm text-gray-600">
                                Cliente: <strong>{{ orden.cliente.nombre }}</strong>
                            </p>
                        </div>

                        <!-- Información de montos -->
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong>Modo de prueba:</strong> El QR es por <strong>{{ testAmount.toFixed(2) }} Bs</strong> 
                                        pero se registrará el pago real de <strong>{{ realAmount.toFixed(2) }} Bs</strong> cuando confirmes.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Información de la orden -->
                        <div class="grid grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-sm text-gray-600">Total de la orden:</p>
                                <p class="text-xl font-bold text-gray-900">{{ orden.total }} Bs</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Saldo pendiente:</p>
                                <p class="text-xl font-bold text-blue-600">{{ orden.saldo_pendiente }} Bs</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Monto del QR (prueba):</p>
                                <p class="text-lg font-semibold text-green-600">{{ testAmount.toFixed(2) }} Bs</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Transacción ID:</p>
                                <p class="text-xs font-mono text-gray-700">{{ transactionId }}</p>
                            </div>
                        </div>

                        <!-- QR Code -->
                        <div v-if="qrBase64" class="flex flex-col items-center gap-4 mb-6">
                            <div class="bg-white p-4 rounded-lg shadow-md">
                                <img 
                                    :src="`data:image/png;base64,${qrBase64}`" 
                                    alt="QR PagoFacil" 
                                    class="w-64 h-64"
                                />
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-2">
                                    📱 Escanea el QR con tu app bancaria
                                </p>
                                <p class="text-xs text-gray-500">
                                    El QR expira el: <strong>{{ expirationDate }}</strong>
                                </p>
                            </div>
                        </div>

                        <div v-else class="text-red-600 p-4 bg-red-50 rounded-lg mb-6">
                            ❌ No se pudo generar el QR. Intenta nuevamente o contacta al administrador.
                        </div>

                        <!-- Mensaje de estado -->
                        <div v-if="mensaje" 
                             :class="{
                                 'bg-green-50 border-green-400 text-green-800': mensajeTipo === 'success',
                                 'bg-red-50 border-red-400 text-red-800': mensajeTipo === 'error',
                                 'bg-blue-50 border-blue-400 text-blue-800': mensajeTipo === 'info',
                             }"
                             class="border-l-4 p-4 mb-6 rounded">
                            <p class="font-medium">{{ mensaje }}</p>
                        </div>

                        <!-- Instrucciones -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <h3 class="font-semibold text-blue-900 mb-2">📋 Instrucciones:</h3>
                            <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
                                <li>Abre tu app bancaria (ej: BCP móvil, Banco Nacional, etc.)</li>
                                <li>Busca la opción de "Pagar con QR" o "Escanear QR"</li>
                                <li>Escanea el código QR mostrado arriba</li>
                                <li>Confirma el pago de <strong>{{ testAmount.toFixed(2) }} Bs</strong></li>
                                <li>Haz clic en "Verificar Pago" o espera la verificación automática</li>
                            </ol>
                            <p class="text-xs text-blue-600 mt-2">
                                ℹ️ El sistema verifica automáticamente cada 10 segundos
                            </p>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex gap-3">
                            <PrimaryButton
                                @click="verificarPago"
                                :disabled="verificando"
                                class="flex-1"
                            >
                                <span v-if="verificando" class="flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Verificando...
                                </span>
                                <span v-else>
                                    🔄 Verificar Pago
                                </span>
                            </PrimaryButton>

                            <SecondaryButton @click="volver" class="flex-1">
                                ⬅️ Volver a la orden
                            </SecondaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
