<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    orden: Object,
});

// Modal de pago
const mostrarModalPago = ref(false);
const mostrarModalQR = ref(false);
const qrGenerado = ref(null);

// Form de pago
const formPago = useForm({
    monto: '',
    metodo: 'EFECTIVO',
    referencia: '',
});

// Abrir modal de pago
const abrirModalPago = () => {
    formPago.reset();
    formPago.monto = props.orden.saldo_pendiente.toFixed(2);
    mostrarModalPago.value = true;
};

// Procesar pago
const procesarPago = () => {
    if (formPago.metodo === 'QR') {
        // Simular generación de QR
        generarQRSimulado();
    } else {
        // Pago en efectivo directo
        registrarPago();
    }
};

// Generar QR simulado
const generarQRSimulado = () => {
    const timestamp = Date.now();
    qrGenerado.value = {
        codigo: `TRX-QR-${timestamp}`,
        imagen: 'https://via.placeholder.com/300x300/4F46E5/FFFFFF?text=QR+SIMULADO',
        monto: formPago.monto,
        orden: props.orden.nro,
    };
    mostrarModalQR.value = true;
};

// Confirmar pago QR
const confirmarPagoQR = () => {
    formPago.referencia = `${qrGenerado.value.codigo} - PagoFácil`;
    registrarPago();
};

// Registrar pago en BD
const registrarPago = () => {
    formPago.post(route('ordenes.pago', props.orden.nro), {
        preserveScroll: true,
        onSuccess: () => {
            mostrarModalPago.value = false;
            mostrarModalQR.value = false;
            qrGenerado.value = null;
            formPago.reset();
        },
        onError: (errors) => {
            console.error('Errores:', errors);
        },
    });
};

// Badges
const getEstadoBadge = (estado) => {
    const badges = {
        'PENDIENTE': 'bg-yellow-100 text-yellow-800',
        'LISTA': 'bg-blue-100 text-blue-800',
        'ENTREGADA': 'bg-green-100 text-green-800',
        'PAGADA': 'bg-purple-100 text-purple-800',
    };
    return badges[estado] || 'bg-gray-100 text-gray-800';
};

const getMetodoBadge = (metodo) => {
    return metodo === 'EFECTIVO' 
        ? 'bg-green-100 text-green-800' 
        : 'bg-blue-100 text-blue-800';
};

// Ir a página de pago QR
const irAPagoQr = () => {
    router.visit(route('ordenes.pago-qr', props.orden.nro));
};
</script>

<template>
    <Head :title="`Orden ${orden.nro}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    📄 Orden {{ orden.nro }}
                </h2>
                <a 
                    href="javascript:history.back()" 
                    class="text-blue-600 hover:text-blue-800"
                >
                    ← Volver
                </a>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Información General -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    
                    <!-- Cliente -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold mb-4">👤 Cliente</h3>
                        <div class="space-y-2">
                            <p><strong>Nombre:</strong> {{ orden.cliente.nombre }}</p>
                            <p><strong>Teléfono:</strong> {{ orden.cliente.telefono }}</p>
                            <p><strong>Dirección:</strong> {{ orden.cliente.direccion }}</p>
                        </div>
                    </div>

                    <!-- Detalles de Orden -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold mb-4">📋 Detalles</h3>
                        <div class="space-y-2">
                            <p>
                                <strong>Estado:</strong>
                                <span 
                                    class="ml-2 px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    :class="getEstadoBadge(orden.estado)"
                                >
                                    {{ orden.estado }}
                                </span>
                            </p>
                            <p><strong>Fecha Recepción:</strong> {{ orden.fecha_recepcion }}</p>
                            <p v-if="orden.fecha_listo"><strong>Fecha Listo:</strong> {{ orden.fecha_listo }}</p>
                            <p v-if="orden.fecha_entrega"><strong>Fecha Entrega:</strong> {{ orden.fecha_entrega }}</p>
                            <p><strong>Forma de Pago:</strong> {{ orden.forma_pago }}</p>
                            <p v-if="orden.fecha_vencimiento">
                                <strong>Fecha Vencimiento:</strong> {{ orden.fecha_vencimiento }}
                            </p>
                            <p v-if="orden.usuario">
                                <strong>Creado por:</strong> {{ orden.usuario.nombre }} ({{ orden.usuario.tipo_usuario }})
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Servicios -->
                <div class="mb-6 bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold mb-4">🧺 Servicios</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Servicio
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                        Cantidad/Peso
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                        Precio Unit.
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                        Fragancia
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                        Subtotal
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                        Descuento
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                        Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="detalle in orden.detalles" :key="detalle.id">
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ detalle.servicio.nombre }}
                                        </div>
                                        <div v-if="detalle.notas" class="text-xs text-gray-500 italic">
                                            {{ detalle.notas }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm">
                                        <span v-if="detalle.unidad === 'KILO'">
                                            {{ detalle.peso_kg }} Kg
                                        </span>
                                        <span v-else>
                                            {{ detalle.cantidad }} pieza(s)
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm">
                                        Bs. {{ parseFloat(detalle.precio_unitario).toFixed(2) }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-500">
                                        {{ detalle.fragancia || '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        Bs. {{ parseFloat(detalle.subtotal).toFixed(2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-red-600">
                                        - Bs. {{ parseFloat(detalle.descuento).toFixed(2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-semibold">
                                        Bs. {{ parseFloat(detalle.total_linea).toFixed(2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totales -->
                    <div class="mt-6 border-t pt-4">
                        <div class="flex justify-end">
                            <div class="w-full md:w-1/3 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span>Subtotal:</span>
                                    <span>Bs. {{ parseFloat(orden.subtotal).toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-red-600">
                                    <span>Descuentos:</span>
                                    <span>- Bs. {{ parseFloat(orden.descuento).toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold border-t pt-2">
                                    <span>TOTAL:</span>
                                    <span>Bs. {{ parseFloat(orden.total).toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Observaciones -->
                <div v-if="orden.observaciones" class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="font-semibold text-yellow-800 mb-2">📌 Observaciones:</h4>
                    <p class="text-yellow-900">{{ orden.observaciones }}</p>
                </div>

                <!-- Pagos -->
                <div class="mb-6 bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">💰 Historial de Pagos</h3>
                        <div v-if="orden.saldo_pendiente > 0" class="flex gap-2">
                            <PrimaryButton 
                                @click="abrirModalPago"
                                class="bg-blue-600 hover:bg-blue-700"
                            >
                                💵 Registrar Pago
                            </PrimaryButton>
                            <PrimaryButton 
                                @click="irAPagoQr"
                                class="bg-green-600 hover:bg-green-700"
                            >
                                📱 Pagar con QR
                            </PrimaryButton>
                        </div>
                    </div>

                    <!-- Estado de Pago -->
                    <div class="mb-4 p-4 bg-gray-50 rounded-md">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p class="text-sm text-gray-600">Total Orden</p>
                                <p class="text-xl font-bold text-gray-900">
                                    Bs. {{ parseFloat(orden.total).toFixed(2) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Pagado</p>
                                <p class="text-xl font-bold text-green-600">
                                    Bs. {{ parseFloat(orden.total_pagado).toFixed(2) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Saldo Pendiente</p>
                                <p class="text-xl font-bold" :class="orden.saldo_pendiente > 0 ? 'text-red-600' : 'text-green-600'">
                                    Bs. {{ parseFloat(orden.saldo_pendiente).toFixed(2) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Pagos -->
                    <div v-if="orden.pagos.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Fecha
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                        Método
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                        Monto
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Referencia
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="pago in orden.pagos" :key="pago.id">
                                    <td class="px-4 py-3 text-sm">
                                        {{ new Date(pago.fecha).toLocaleString('es-BO') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span 
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="getMetodoBadge(pago.metodo)"
                                        >
                                            {{ pago.metodo }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-semibold">
                                        Bs. {{ parseFloat(pago.monto).toFixed(2) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        {{ pago.referencia || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        <p>No se han registrado pagos aún</p>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Registrar Pago -->
        <Modal :show="mostrarModalPago" @close="mostrarModalPago = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">💵 Registrar Pago</h3>
                
                <form @submit.prevent="procesarPago">
                    
                    <div class="mb-4">
                        <InputLabel for="monto" value="Monto (Bs.) *" />
                        <TextInput
                            id="monto"
                            type="number"
                            step="0.01"
                            v-model="formPago.monto"
                            class="mt-1 w-full"
                            :max="orden.saldo_pendiente"
                            required
                        />
                        <InputError class="mt-2" :message="formPago.errors.monto" />
                        <p class="mt-1 text-sm text-gray-500">
                            Saldo pendiente: Bs. {{ parseFloat(orden.saldo_pendiente).toFixed(2) }}
                        </p>
                    </div>

                    <div class="mb-4">
                        <InputLabel for="metodo" value="Método de Pago *" />
                        <select
                            id="metodo"
                            v-model="formPago.metodo"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            required
                        >
                            <option value="EFECTIVO">💵 EFECTIVO</option>
                            <option value="QR">📱 QR (PagoFácil)</option>
                        </select>
                        <InputError class="mt-2" :message="formPago.errors.metodo" />
                    </div>

                    <div class="flex justify-center gap-3">
                        <SecondaryButton @click="mostrarModalQR = false">
                            Cancelar
                        </SecondaryButton>
                        <PrimaryButton @click="confirmarPagoQR" :disabled="formPago.processing">
                            ✅ Confirmar Pago
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal QR Simulado -->
        <Modal :show="mostrarModalQR" @close="mostrarModalQR = false">
            <div class="p-6 text-center">
                <h3 class="text-lg font-semibold mb-4">📱 QR Generado - PagoFácil (SIMULADO)</h3>
                
                <div class="mb-6">
                    <img 
                        :src="qrGenerado?.imagen" 
                        alt="QR Code" 
                        class="mx-auto rounded-lg shadow-lg"
                    />
                </div>

                <div class="mb-4 text-left bg-gray-50 p-4 rounded-md">
                    <p><strong>Orden:</strong> {{ qrGenerado?.orden }}</p>
                    <p><strong>Monto:</strong> Bs. {{ qrGenerado?.monto }}</p>
                    <p><strong>Código:</strong> {{ qrGenerado?.codigo }}</p>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3 mb-4">
                    <p class="text-sm text-yellow-800">
                        ⚠️ <strong>Simulación:</strong> Este es un QR simulado. 
                        En producción, aquí se mostraría el QR real de PagoFácil y se esperaría 
                        el callback de confirmación.
                    </p>
                </div>

                <div class="flex justify-center gap-3">
                    <SecondaryButton @click="mostrarModalQR = false">
                        Cancelar
                    </SecondaryButton>
                    <PrimaryButton @click="confirmarPagoQR" :disabled="formPago.processing">
                        ✅ Confirmar Pago
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
