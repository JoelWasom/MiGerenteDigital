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
                                        <label for="tipoPago">Cliente</label>

                                        <v-select v-model="selectedCliente" :options="gntCliente" label="title"
                                            placeholder="Seleccionar Cliente" class="select-size-lg" :max-options="3">

                                        </v-select>
                                    </b-form-group>
                                </b-col>
                            </b-row>
                            <b-row>
                                <b-col sm="12" md="12" xl="12">
                                    <b-form-group>
                                        <label for="tipoPago">Tipo Venta</label>

                                        <v-select v-model="selectedTipoVenta" :options="tipoVenta" label="title"
                                            placeholder="Seleccionar" class="select-size-lg" :max-options="3">

                                        </v-select>
                                    </b-form-group></b-col>

                            </b-row>
                            <b-row>

                                <b-col sm="12" md="12" xl="12">
                                    <b-form-group>
                                        <label for="tipoPago">Forma de Pago</label>
                                        <v-select v-model="SelectedtipoPago" :options="tiposPago" label="title"
                                            placeholder="Seleccionar" class="select-size-lg" :max-options="3">
                                        </v-select>
                                    </b-form-group>
                                </b-col>
                            </b-row>
                            <b-row>
                                <b-col>
                                    <b-form-group>
                                        <label>Total a Pagar</label>
                                        <b-form-input v-model="totalPagarCalculado" disabled class="montos"></b-form-input>
                                    </b-form-group>
                                </b-col>
                            </b-row>

                            <b-row>
                                <b-col>
                                    <b-form-group>
                                        <label for="montoRecibido">Monto Recibido</label>
                                        <b-form-input id="montoRecibido" v-model="montoRecibido"
                                            class="montos"></b-form-input>
                                    </b-form-group>
                                </b-col>
                                <b-col>
                                    <b-form-group>
                                        <label>Cambio</label>
                                        <b-form-input v-model="cambio" class="montos" disabled></b-form-input>
                                    </b-form-group>
                                </b-col>

                            </b-row>


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
                                            class="select-size-lg" :max-options="3" @input="cargarProducto()">
                                            <template #option="{ title, icon, precioV, cantidad }">

                                                <div class="d-flex align-items-center">
                                                    <div class="product-image-container">
                                                        <img v-if="icon" :src="'data:image/jpeg;base64,' + icon"
                                                            alt="Sin Foto" />
                                                    </div>
                                                    <div class="product-details">
                                                        <strong>{{ title }}</strong>
                                                        <div class="text-secondary ">Precio: {{ precioV }}</div>
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
                                    <b-table id="tabla-lista-retrasos" :items="itemsAgregado" :fields="fieldsAgregado"
                                        :filter="filter" @filtered="onFiltered" hover :busy="isBusy" :bordered="true"
                                        :fixed="true" :sticky-header="stickyHeader" :head-variant="headVariant">

                                        <template #cell(cantidad)="row">
                                            <b-form-input v-model="row.value" type="number" min="1"
                                                @input="actualizarCantidad(row.item, row.value)" ref="cantidadInput"
                                                :state="row.value > 0 ? true : false"
                                                v-b-tooltip.hover.top.right="row.value > 0 ? '' : 'Cantidad debe ser mayor que cero'"
                                                :show="row.value === 0" :trigger="'hover focus'"
                                                class="v-b-tooltip-dark text-center" />
                                        </template>
                                        <template #cell(subtotal)="row">
                                            {{ row.item.precioV * row.item.cantidad }}
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
            gntCliente: [],
            selectedCliente: null,
            tipoVenta: [{ id: "0", title: "CONTADO" }, { id: "1", title: "CREDITO" }],
            selectedTipoVenta: null,
            totalPagar: 0, // Total a pagar
            montoRecibido:0,
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
                precioV: 0,
                cantidad: 0,
                subtotal: 0
            },

            itemsAgregado: [],
            fieldsAgregado: [
                { key: "id", label: "codigo", sortable: true, tdClass: "text-center text-bold" },
                { key: "title", label: "PRODUCTO", sortable: true, tdClass: "text-left" },
                { key: "precioV", label: "PRECIO", sortable: true, tdClass: "text-center text-bold" },
                { key: "cantidad", label: "CANTIDAD", sortable: false, tdClass: "text-center text-bold" },
                { key: "subtotal", label: "SUBTOTAL", sortable: false, tdClass: "text-center text-bold" },
                { key: "Acción", sortable: false, tdClass: "text-center" },
            ],
        }
    },
    watch: {
        montoRecibido: function (nuevoMonto) {
            // Calcular el cambio
            this.cambio = nuevoMonto - this.totalPagar

        }
    },
    directives: {
        "b-tooltip": VBTooltip,
        Ripple,
    },

    mounted() {
        this.cbxArticulo()
        this.cbxFormaPago()
        this.cbxCliente()
    },
    computed: {
        totalPagarCalculado() {
            return this.itemsAgregado.reduce((total, item) => {
                this.totalPagar = total + item.precioV * item.cantidad
                this.montoRecibido = 0;
                this.cambio = 0;
                return this.totalPagar;
            }, 0);

        },
    },
    methods: {
        // imprimirTabla() {
        //     printJS({
        //         printable: 'frm-articulo',
        //         type: 'html',
        //         importCSS: true,  // Importar estilos CSS
        //         printContainer: false,

        //     });
        //   window.print();
        // },
        // Alertas 
        UsuarioAlerta(variant, msj) {
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
            const params = new URLSearchParams();
            // params.append('email', me.email);
            // me.tiposPago = [];
            me.isBusy = true;
            var url = "api/auth/ListaFormaPago";
            me.loaded = false;
            var lista = [];

            axios
                .get(url)
                .then(function (response) {
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
                })
                .catch((e) => {
                    UsuarioAlerta("error", e.response.data.error);
                });

        },


        cbxArticulo() {
            let me = this;
            const axios = require("axios").default;
            const params = new URLSearchParams();
            // params.append('email', me.email);
            me.items = [];
            me.isBusy = true;
            var url = "api/auth/listArticulo";
            me.loaded = false;
            var lista = [];
            axios
                .get(url)
                .then(function (response) {
                    var resp = response.data;
                    for (let i = 0; i < resp.length; i++) {
                        lista.push({
                            id: resp[i].artId,
                            title: resp[i].artNombre,
                            icon: resp[i].artFoto,
                            precioV: resp[i].artPrecioVenta,
                            cantidad: resp[i].artCantidad
                        });
                    }
                    me.booksProductos = lista;
                    me.isBusy = false;
                    me.loaded = true;
                })
                .catch((e) => {
                    alert("error al obtener los datos Lista Articulo " + e);
                });
        },


        cbxCliente() {

            let me = this;
            const axios = require("axios").default;
            const params = new URLSearchParams();
            // params.append('email', me.email);
            me.items = [];
            me.isBusy = true;
            var url = "api/auth/ListaCliente";
            me.loaded = false;
            var lista = [];
            axios
                .get(url)
                .then(function (response) {
                    var resp = response.data;
                    for (let i = 0; i < resp.length; i++) {
                        lista.push({
                            id: resp[i].cliId,
                            title: resp[i].cliNombre + " " + resp[i].cliApp + " " + resp[i].cliApm,
                        });
                    }
                    me.gntCliente = lista;
                    me.isBusy = false;
                    me.loaded = true;
                })
                .catch((e) => {
                    alert("error al obtener los datos Lista Articulo " + e);
                });

        },
        cargarProducto() {
            if (this.selectedProductos) {
                // Verificar si el producto ya está en la lista itemsAgregado
                const productoExistente = this.itemsAgregado.find(item => item.id === this.selectedProductos.id);

                if (!productoExistente) {
                    // Si el producto no existe en la lista, lo agregamos
                    this.itemsAgregado.push({ ...this.selectedProductos, cantidad: 1, subtotal: this.selectedProductos.precioV });
                } else {
                    // Si el producto ya existe, puedes mostrar un mensaje de alerta o realizar otra acción
                    this.UsuarioAlerta("error", 'El producto ya está en la lista');

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
            me.selectedCliente = null
            me.selectedTipoVenta = null
            me.SelectedtipoPago = null
            me.itemsAgregado = []
            me.montoRecibido = 0
            me.cambio = 0
        },

        GurdarMovimientoCaja() {

            const me = this;
            const axios = require("axios").default;
            const formData = new FormData();

            formData.append("cajId", 1);
            formData.append("userId", this.$store.state.app.UsuarioId); // ID del usuario actual
            formData.append("cjtReferencia", me.cjtReferencia);
            formData.append("cjtModulo", "VENTAS");
            formData.append("cjtMonto", me.totalPagar)
            formData.append("cjtDescripcion", "Ingresos Por Venta");
            formData.append("ttxnId", 1);
            axios
                .post("api/auth/guardarMovimientoCaja", formData, {
                    headers: {
                        "Content-Type": "multipart/form-data"
                    }
                })
                .then(function (response) {
                    if (response.status === 201) {
                    console.log(response.data.mensaje)

                    }
                })
                .catch((e) => {
                    me.showOverlay = false;
                    me.UsuarioAlerta("error", e.response.data.error);
                });


        },
        Guardar() {
            const me = this;
            me.showOverlay = true;
            const hoy = new Date();
            const axios = require("axios").default;
            const formData = new FormData();
            if (me.selectedCliente === null || me.selectedTipoVenta === null || me.SelectedtipoPago === null) {
                return me.UsuarioAlerta("error", "Faltan Datos Para Ingresar")
            }
            formData.append("cliId", me.selectedCliente.id); //  ID del cliente
            formData.append("userId", this.$store.state.app.UsuarioId); // ID del usuario actual
            formData.append("ttxId", 1); //ID del tipo de transacción 
            formData.append("vntCredito", me.selectedTipoVenta.id); // Esto puede ser 1 o 0 según corresponda (crédito o no)
            formData.append("fpId", me.SelectedtipoPago.id)  //Forma de pago 
            formData.append("vntActivo", 1); // Esto puede ser 1 o 0 según corresponda (activo o no)
            // Construye un array de detalles de venta
            const detallesVenta = this.itemsAgregado.map(item => ({
                artId: item.id, // ID del artículo
                vndCantidad: item.cantidad, // Cantidad vendida
                vndPrecioVenta: item.precioV, // Precio de venta
                vndDescuento: 0, // Descuento (ajusta según necesites)
                vndActivo: 1 // Activo (ajusta según necesites)
            }));
            if (detallesVenta.length <= 0) {
                return me.UsuarioAlerta("error", "Debe Selecinar Ariculos")

            }
            formData.append("detallesVenta", JSON.stringify(detallesVenta));
            axios
                .post("api/auth/guardarVenta", formData, {
                    headers: {
                        "Content-Type": "multipart/form-data"
                    }
                })
                .then(function (response) {
                    if (response.status === 201) {
                        me.showOverlay = false;
                        me.UsuarioAlerta("success", response.data.mensaje);
                        me.cjtReferencia = response.data.cjtReferencia;
                        me.GurdarMovimientoCaja()
                        me.isBusy = false;
                        me.vaciarControles()
                    }
                })
                .catch((e) => {
                    me.showOverlay = false;
                    me.UsuarioAlerta("error", e.response.data.error);
                });
        },
        actualizarCantidad(item, nuevaCantidad) {
            item.cantidad = nuevaCantidad;
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
            this.$bvModal
                .msgBoxConfirm(
                    "El Registro  " + " : " + item["title"] + " Serán Eliminados",
                    {
                        title: "Advertencia",
                        size: "sm",
                        okVariant: "outline-success",
                        okTitle: "Continuar",
                        cancelTitle: "Cancelar",
                        cancelVariant: "outline-danger",
                        hideHeaderClose: true,
                        centered: true,


                    }
                )
                .then((value) => {
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