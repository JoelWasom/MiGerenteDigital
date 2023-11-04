<template>
    <section>
        <b-row>
            <b-col sm="12" md="3" xl="3">
                <b-row>
                    <b-col>
                        <b-card border-variant="info">

                            <b-row>
                                <b-col sm="12" md="12" xl="12">

                                    <b-form-group>
                                        <label for="tipoPago">Proveedor</label>
                                        <v-select label="title" placeholder="Seleccionar Proveedor" class="select-size-lg" 
                                            v-model="selectedProveedor" 
                                            :options="gntProveedor" 
                                            :max-options="3">
                                        </v-select>
                                    </b-form-group>
                                </b-col>
                            </b-row>
                            <!-- <b-row>
                                <b-col sm="12" md="12" xl="12">
                                    <b-form-group>
                                        <label for="tipoPago">Tipo Compra</label>
                                        <v-select label="title" placeholder="Seleccionar" class="select-size-lg"
                                            v-model="selectedTipoCompra" 
                                            :options="tipoCompra" 
                                            :max-options="3">
                                        </v-select>
                                    </b-form-group>
                                </b-col>
                            </b-row>
                            <b-row>
                                <b-col sm="12" md="12" xl="12">
                                    <b-form-group>
                                        <label for="tipoPago">Forma de Pago</label>
                                        <v-select label="title" placeholder="Seleccionar" class="select-size-lg"
                                            v-model="SelectedtipoPago" 
                                            :options="tiposPago" 
                                            :max-options="3">
                                        </v-select>
                                    </b-form-group>
                                </b-col>
                            </b-row> -->
                            <b-row>
                                <b-col sm="12" md="12" xl="12">
                                    <b-form-group>
                                        <label for="tipoPago">Fecha de Compra:</label>
                                        <b-form-datepicker v-model="txtFechaCompra"></b-form-datepicker>
                                    </b-form-group>
                                </b-col>
                            </b-row>
                            <b-row>
                                <b-col>
                                    <b-form-group>
                                        <label>Total a Pagar</label>
                                        <b-form-input disabled class="montos" v-model="totalPagarCalculado"></b-form-input>
                                    </b-form-group>
                                </b-col>
                            </b-row>
                            <!-- <b-row >
                                <b-col>
                                    <b-form-group>
                                        <label for="montoRecibido">Monto Recibido</label>
                                        <b-form-input id="montoRecibido" class="montos" v-model="montoRecibido"></b-form-input>
                                    </b-form-group>
                                </b-col>
                                <b-col>
                                    <b-form-group>
                                        <label>Cambio</label>
                                        <b-form-input class="montos" disabled v-model="cambio"></b-form-input>
                                    </b-form-group>
                                </b-col>
                            </b-row> -->
                        </b-card>
                    </b-col>
                </b-row>
            </b-col>
            <b-col sm="12" md="9" xl="9" class="mb-1">
                <b-row>
                    <b-col>
                        <b-card border-variant="info">
                            <b-row>
                                <b-col>
                                    <b-form-group>
                                        <label class="d-inline d-lg-flex">Buscar Producto</label>
                                        <v-select ref="selectedProductos" v-model="selectedProductos"
                                            :options="booksProductos" label="title" placeholder="Seleccionar"
                                            class="select-size-lg" :max-options="3" @input="AgregarProductoDetalle()">
                                            <template #option="{ title, icon, cantidad }">

                                                <div class="d-flex align-items-center">
                                                    <div class="product-image-container">
                                                        <img v-if="icon" :src="'data:image/jpeg;base64,' + icon"
                                                            alt="Sin Foto" />
                                                    </div>
                                                    <div class="product-details">
                                                        <strong>{{ title }}</strong>
                                                        <div class="text-secondary ">Stock: {{ cantidad }}</div>
                                                    </div>
                                                </div>
                                            </template>
                                        </v-select>
                                    </b-form-group>
                                </b-col>
                            </b-row>
                            <b-row>
                                <b-col>
                                    <!-- Tabla --> <!-- Listado -->
                                    <b-table id="tabla-lista-detalle" :items="itemsAgregado" :fields="fieldsAgregado"
                                        :filter="filter" @filtered="onFiltered" hover :busy="isBusy" :bordered="true"
                                        :fixed="true" :sticky-header="stickyHeader" :head-variant="headVariant">
                                        <template #cell(precioC)="row">
                                            <b-form-input v-model="row.value" type="number" min="0"
                                                @input="actualizarSubtotal(row.item, row.value)" ref="PrecioInput"
                                                :state="row.value > 0 ? true : false"
                                                v-b-tooltip.hover.top.right="row.value > 0 ? '' : 'Precio debe ser mayor a cero'"
                                                :show="row.value === 0" :trigger="'hover focus'"
                                                class="v-b-tooltip-dark text-center" />
                                        </template>
                                        <template #cell(cantidad)="row">
                                            <b-form-input v-model="row.value" type="number" min="1"
                                                @input="actualizarCantidad(row.item, row.value)" ref="cantidadInput"
                                                :state="row.value > 0 ? true : false"
                                                v-b-tooltip.hover.top.right="row.value > 0 ? '' : 'Cantidad debe ser mayor que cero'"
                                                :show="row.value === 0" :trigger="'hover focus'"
                                                class="v-b-tooltip-dark text-center" />
                                        </template>
                                        <template #cell(subtotal)="row">
                                            {{ row.item.precioC * row.item.cantidad }}
                                        </template>
                                        <template #cell(Acción)="row">

                                            <b-button v-ripple.400="'rgba(255, 255, 255, 0.15)'" variant="flat-danger"
                                                class="btn-icon rounded-circle"
                                                :class="{ 'd-none': $store.state.app.isElimina }"
                                                @click="clickAccion(row.item, row.index, ('eliminar'))">
                                                <feather-icon icon="TrashIcon" />
                                            </b-button>
                                        </template>
                                        <template #table-busy>
                                            <div class="text-center text-danger my-2">
                                                <b-spinner class="align-middle"></b-spinner>
                                                <strong>Cargando...</strong>
                                            </div>
                                        </template>
                                    </b-table>

                                </b-col>
                            </b-row>
                        </b-card>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col>
                        <b-button v-ripple.400="'rgba(255, 255, 255, 0.15)'" :variant="$store.state.app.variant"
                            :class="$store.state.app.classButton" @click="validaOperacion($store.state.app.TipoAccion)">
                            <feather-icon :icon="$store.state.app.botonIcono" class="mr-50" />
                            <span class="align-middle">{{ $store.state.app.botonTexto }} </span>
                        </b-button>
                    </b-col>

                </b-row>
            </b-col>
        </b-row>


    </section>
</template>
<script>
import {
    VBTooltip,
    BFormSpinbutton,
    BImg,
    BFormFile,
    BFormDatepicker,
    BRow,
    BModal,
    VBModal,
    BAvatar,
    BCardTitle,
    BCardBody,
    BCardHeader,
    BCard,
    BDropdown,
    BDropdownItem,
    BButton,
    BFormSelect,
    BCol,
    BFormGroup,
    BFormInput,
    BFormCheckbox,
    BForm,
    BFormText,
    BFormDatalist,
    BBadge,
    BTable,
    BMedia,
    BFormTextarea,
    BInputGroupAppend,
    BInputGroup,
    BOverlay,
    BSpinner,
    BFormValidFeedback,
    BFormInvalidFeedback,
    BIconNutFill,
} from "bootstrap-vue";
import Ripple from "vue-ripple-directive";
import vSelect from 'vue-select'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
export default {
    components: {
        VBTooltip,
        ToastificationContent,
        BImg,
        vSelect,
        BFormFile,
        BFormValidFeedback,
        BFormInvalidFeedback,
        BOverlay,
        BFormDatepicker,
        BInputGroupAppend,
        BInputGroup,
        BRow,
        BModal,
        VBModal,
        BTable,
        BAvatar,
        BCardTitle,
        BCardBody,
        BCardHeader,
        BCard,
        BDropdown,
        BDropdownItem,
        BButton,
        BFormSelect,
        BFormTextarea,
        BCol,
        BFormGroup,
        BFormInput,
        BFormCheckbox,
        BForm,
        BMedia,
        BFormText,
        BFormDatalist,
        BBadge,
        BSpinner,
        BFormSpinbutton
    },
    data() {
        return {
            tiposPago: [],
            SelectedtipoPago: null, // Tipo de pago seleccionado
            gntProveedor: [],
            selectedProveedor: null,
            tipoCompra: [{ id: "0", title: "CONTADO" }, { id: "1", title: "CREDITO" }],
            selectedTipoCompra: null,
            totalPagar: 0, // Total a pagar
            montoRecibido:0,
            txtFechaCompra: null,
            cjtReferencia: 0, // Monto recibido
            cambio: 0, // Cambio a entregar
            vntNumero: "",
            isBusy: false,
            filter: "",
            stickyHeader: true,
            headVariant: "dark",
            booksProductos: [
            ],
            selectedProductos: {
                id: "0",
                title: "",
                icon: 'ListIcon',
                precioC: 0,
                cantidad: 0,
                subtotal: 0
            },

            itemsAgregado: [],
            fieldsAgregado: [
                { key: "id", label: "codigo", sortable: true, tdClass: "text-center text-bold" },
                { key: "title", label: "PRODUCTO", sortable: true, tdClass: "text-left" },
                { key: "precioC", label: "PRECIO", sortable: true, tdClass: "text-center text-bold" },
                { key: "cantidad", label: "CANTIDAD", sortable: false, tdClass: "text-center text-bold" },
                { key: "subtotal", label: "SUBTOTAL", sortable: false, tdClass: "text-center text-bold" },
                { key: "Acción", sortable: false, tdClass: "text-center" },
            ],
        }
    },
    watch: {
        // montoRecibido: function (nuevoMonto) {
        //     // Calcular el cambio
        //     this.cambio = nuevoMonto - this.totalPagar

        // }
    },
    directives: {
        "b-tooltip": VBTooltip,
        Ripple,
    },

    mounted() {
        this.cbxArticulo()
        this.cbxFormaPago()
        this.cbxProveedor()
    },
    computed: {
        totalPagarCalculado() {
            return this.itemsAgregado.reduce((total, item) => {
                this.totalPagar = total + item.precioC * item.cantidad
                this.montoRecibido = 0;
                this.cambio = 0;
                return this.totalPagar;
            }, 0);

        },
    },
    methods: {
        /**
         * Funcion para notificar alertas de las eventualidades (Success, Warning, Error, etc)
         *  */ 
        AlertaMensaje(variant, msj) {
            let title, confirmButtonClass, showClass;
            if (variant === "success") {
                title = "Buen Trabajo";
                confirmButtonClass = "btn btn-success";
                showClass = "animate__animated animate__bounceIn";
            } else if (variant === "error") {
                title = "¡Error!";
                confirmButtonClass = "btn btn-danger";
                showClass = "btn btn-danger animate__animated animate__rubberBand";
            } else if (variant === "warning") {
                title = "Precaución";
                confirmButtonClass = "btn btn-warning";
                showClass = "animate__animated animate__wobble";
            } else {
                // Puedes agregar más casos según tus necesidades.
            }
            this.$swal({
                title: title,
                text: msj,
                icon: variant,
                customClass: {
                    confirmButton: confirmButtonClass,
                },
                showClass: {
                    confirmButton: showClass,
                },
                buttonsStyling: true,
            });
        },

        //eventos 
        cbxFormaPago() {
            let me = this;
            const axios = require("axios").default;
            me.isBusy = true;
            var url = "api/auth/ListaFormaPago";
            me.loaded = false;
            var lista = [];
            axios.get(url).then(function (response) {
                var resp = response.data;
                for (let i = 0; i < resp.length; i++) {
                    lista.push({
                        id: resp[i].fpId,
                        title: resp[i].fpnombre,
                    });
                }
                me.tiposPago = lista;
                me.isBusy = false;
                me.loaded = true;
            }).catch((e) => {
                AlertaMensaje("error", "Obtener Datos Forma de Pago, Detalles: ", e.response.data.error);
            });
        },
        cbxArticulo() {
            let me = this;
            const axios = require("axios").default;
            me.items = [];
            me.isBusy = true;
            var url = "api/auth/listArticulo";
            me.loaded = false;
            var lista = [];
            axios.get(url).then(function (response) {
                var resp = response.data;
                for (let i = 0; i < resp.length; i++) {
                    lista.push({
                        id: resp[i].artId,
                        title: resp[i].artNombre,
                        icon: resp[i].artFoto,
                        precioC: resp[i].artCosto,
                        cantidad: resp[i].artCantidad
                    });
                }
                me.booksProductos = lista;
                me.isBusy = false;
                me.loaded = true;
            }).catch((e) => {
                AlertaMensaje("error", "Obtener Datos de Articulos, Detalles: "+ e.response.data.error);
            });
        },
        cbxProveedor() {
            let me = this;
            const axios = require("axios").default;
            me.items = [];
            me.isBusy = true;
            var url = "api/auth/listarProveedores";
            me.loaded = false;
            var lista = [];
            axios.get(url).then(function (response) {
                var resp = response.data;
                for (let i = 0; i < resp.length; i++) {
                    lista.push({
                        id: resp[i].provID,
                        title: resp[i].provNombre + " - " + resp[i].provTelefono,
                    });
                }
                me.gntProveedor = lista;
                me.isBusy = false;
                me.loaded = true;
            }).catch((e) => {
                AlertaMensaje("error", "Obtener Datos de Proveedores, Detalles: "+ e.response.data.error);
            });
        },
        AgregarProductoDetalle() {
            if (this.selectedProductos) {
                // Verificar si el producto ya está en la lista itemsAgregado
                const productoExistente = this.itemsAgregado.find(item => item.id === this.selectedProductos.id);

                if (!productoExistente) {
                    // Si el producto no existe en la lista, lo agregamos
                    this.itemsAgregado.push({ ...this.selectedProductos, cantidad: 1, subtotal: this.selectedProductos.precioC });
                } else {
                    // Si el producto ya existe, puedes mostrar un mensaje de alerta o realizar otra acción
                    this.AlertaMensaje("warning", 'El articulo seleccionado ya está en la lista');
                }
                // Resetea el valor de selectedProductos para deseleccionar la opción
                this.selectedProductos = null;
                this.$nextTick(() => {
                    this.$refs.cantidadInput.focus();
                });
            }
        },
        //Metodos Con logica de Negocios
        vaciarControles() {
            const me = this
            me.selectedProveedor = null
            me.itemsAgregado = []
            me.montoRecibido = 0
            me.cambio = 0
        },

        GurdarMovimientoCaja() {
            const me = this;
            const axios = require("axios").default;
            const formData = new FormData();
            formData.append("cajId", 2); // 2=Gastos de Compra
            formData.append("userId", this.$store.state.app.UsuarioId); // ID del usuario actual
            formData.append("cjtReferencia", me.cjtReferencia);
            formData.append("cjtModulo", "COMPRAS");
            formData.append("cjtMonto", me.totalPagar)
            formData.append("cjtDescripcion", "Egresos Por Compra");
            formData.append("ttxnId", 2); // 2=Egresos
            axios.post("api/auth/guardarMovimientoCaja", formData, {
                headers: {"Content-Type": "multipart/form-data"}
            }).then(function (response) {
                if (response.status === 201) {
                    console.log(response.data.mensaje)
                }
            }).catch((e) => {
                me.showOverlay = false;
                me.AlertaMensaje("error", "Guardar Movimiento en Caja, Detalles: ",e.response.data.error);
            });
        },
        Guardar() {
            const me = this;
            me.showOverlay = true;
            const hoy = new Date();
            const axios = require("axios").default;
            const formData = new FormData();
            // if (me.selectedProveedor === null || me.selectedTipoCompra === null || me.SelectedtipoPago === null) {
            //     return me.AlertaMensaje("error", "Faltan Datos Para Ingresar")
            // }
            if (me.selectedProveedor === null) {
                return me.AlertaMensaje("warning", "Faltan completar campos requeridos para el registro..")
            }
            if (me.txtFechaCompra === null) {
                return me.AlertaMensaje("warning", "Faltan completar campos requeridos para el registro..")
            }
            formData.append("provId", me.selectedProveedor.id); //  ID del Proveedor
            formData.append("userId", this.$store.state.app.UsuarioId); // ID del usuario actual
            formData.append("txnId", 2); //ID del tipo de transacción 1=Ingreso, 2=Egreso
            formData.append("cmtFechaCompra",me.txtFechaCompra)
            formData.append("cmtActivo", 1); // Esto puede ser 1 o 0 según corresponda (activo o no)
            // Construye un array de detalles de Compra
            const detallesCompra = this.itemsAgregado.map(item => ({
                artId: item.id, // ID del artículo
                cmdCantidad: item.cantidad, // Cantidad vendida
                cmdCosto: item.precioC, // Precio de Compra
                vndActivo: 1 // Activo (ajusta según necesites)
            }));
            if (detallesCompra.length <= 0) {
                return me.AlertaMensaje("warning", "Debe agregar al menos un producto para realizar el registro de compra.")
            }
            formData.append("detallesCompra", JSON.stringify(detallesCompra));
            axios.post("api/auth/addShopping", formData, {
                headers: { "Content-Type": "multipart/form-data" }
            }).then(function (response) {
                if (response.status === 201) {
                    me.showOverlay = false;
                    me.cjtReferencia = response.data.cjtReferencia;
                    me.AlertaMensaje("success", response.data.mensaje);
                    me.GurdarMovimientoCaja()
                    me.isBusy = false;
                    me.vaciarControles()
                }
            }).catch((e) => {
                me.showOverlay = false;
                me.AlertaMensaje("error", "Guardar Registro Compra, Detalles: ", e.response.data.error);
            });
        },
        actualizarCantidad(item, nuevaCantidad) {
            item.cantidad = nuevaCantidad;
        },
        actualizarSubtotal(item,precio){
            item.precioC = precio
            item.subtotal = item.precioC * item.cantidad
        },
        onFiltered(filteredItems) {
            // Trigger pagination to update the number of buttons/pages due to filtering
            this.totalRows = filteredItems.length;
        },
        eliminarProducto(index) {
            if (index >= 0 && index < this.itemsAgregado.length) {
                this.itemsAgregado.splice(index, 1);
                this.montoRecibido = 0
                this.cambio=0

            }
        },
        ControlaEliminar(item, index) {
            this.boxTwo = "";
            this.$bvModal.msgBoxConfirm(
                "El Registro  " + " : " + item["title"] + " Serán Eliminados",
                {
                    title: "Advertencia",
                    size: "sm",
                    okVariant: "outline-success",
                    okTitle: "Continuar",
                    cancelTitle: "Cancelar",
                    cancelVariant: "outline-danger",
                    hideHeaderClose: true,
                    centered: true
                }
            ).then((value) => {
                this.boxTwo = value;
                if (value === true) {
                    this.eliminarProducto(index);
                }
            });
        },

        //Realiza la Operacion Correspondiente
        validaOperacion(accion) {
            if (accion === "guardar") { this.Guardar() }
            if (accion === "editar") { this.mofificar() }
            if (accion === "ver") { alert("ajecutara el ver") }

        },
        //Este evento elimina Articulo del Carrito
        clickAccion(item, index, accion) {
            if (accion === "eliminar") {
                this.ControlaEliminar(item, index)
            }
        },
    }
}
</script>

<style lang="scss">
@import '~@resources/scss/vue/libs/vue-select.scss';
@import "~@resources/scss/base/pages/app-ecommerce-details.scss";
@import '~@resources/scss/vue/libs/vue-sweetalert.scss';

.product-image-container {
    width: 60px;
    height: 60px;
    margin-right: 10px;
    overflow: hidden;
    border-radius: 4px;
}

.product-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-details {
    flex-grow: 1;
    margin-left: 10px;
    /* Agrega un margen a la izquierda */
}

/* Estilos para dispositivos móviles */
@media screen and (max-width: 767px) {
    .form-textarea {
        font-size: 16px;
        /* Tamaño de letra para dispositivos móviles */
    }
}

/* Estilos para dispositivos no móviles */
@media screen and (min-width: 768px) {
    .form-textarea {
        font-size: 15px;
        /* Tamaño de letra para dispositivos no móviles */
    }
}

.form-textarea {
    text-transform: uppercase;
}


//para los totales ,recibido, cambios
.montos {
    font-size: 24px;
    /* Ajusta el tamaño de fuente según tus preferencias */
    font-weight: bold;
    /* Hace que el texto sea negrita */
    color: #007bff;
    /* Cambia el color del texto, si lo deseas */
    /* Agrega otros estilos según tus preferencias */
}
</style>