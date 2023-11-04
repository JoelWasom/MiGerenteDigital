   
<template>
    <section>
        <b-row>
            <b-col xl="3" v-for="(item, index) in metadataListado" :key="index">
                <b-card class="ecommerce-card">

                    <b-card-body>

                        <b-row>
                            <b-col>
                                <div class="item-img  card-img-top bg-transparent text-center">
                                 
                                    <b-img fluid class="card-img-top product-img" :src="item.artFoto" v-if="item.artFoto"
                                        style="width: 250px; height: 250px; margin: auto;" />

                                </div>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col class="text-center ">

                                <h6>
                                    ${{ item.artPrecioVenta }}
                                </h6>

                            </b-col>

                        </b-row>
                        <b-row class="align-items-center">
                            <b-col>
                                <h6>
                                    {{ item.artNombre }}
                                </h6>
                            </b-col>

                        </b-row>

                    </b-card-body>

                    <!-- Action Buttons -->

                </b-card>
            </b-col>
        </b-row>


    </section>
</template>
<script>
import {
    BCardText,
    BImg,
    BButtonToolbar,
    BButtonGroup,
    VBTooltip,
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
} from "bootstrap-vue";
import Ripple from "vue-ripple-directive";
import vSelect from 'vue-select'


export default {
    components: {
        BCardText,
        BImg,
        BButtonToolbar,
        BButtonGroup,
        VBTooltip,
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

    },
    directives: {
        "b-tooltip": VBTooltip,
        Ripple,
    },
    data() {
        return {
            metadataListado: [
                {
                    artNombre: ' ENCHUFE E INTERRUPTOR DE 2 X 4 DE PARED ',
                    artPrecioVenta: 10200,
                    artFoto: require('@/assets/images/banner/banner-1.jpg'),
                },
                {
                    artNombre: 'Articulo 1',
                    artPrecioVenta: 10200,
                    artFoto: require('@/assets/images/banner/banner-2.jpg'),
                },
                {
                    artNombre: 'Articulo 1',
                    artPrecioVenta: 10200,
                    artFoto: require('@/assets/images/banner/banner-3.jpg'),
                },
                {
                    artNombre: 'Articulo 1',
                    artPrecioVenta: 10200,
                    artFoto: require('@/assets/images/banner/banner-4.jpg'),
                },
                // Agrega más objetos con datos ficticios aquí
            ],
            TipoAccion: null,
            stickyHeader: true,
            transProps: {
                // Transition name
                name: "flip-list",
            },
            evento: "",
            promotores: "",
            pasivos: "",
            detractores: "",
            mesReporte: "",

            shows: true,
            isBusy: false,
            totalRows: 1,
            loaded: false,
            filter: "",
            filterOn: [],
            idGenera: 0,
            id: 0,
            fecha: 0,
            fechaRegistro: "",
            Loading: "",
            estado: "",
            items: [],
            fields: [
                { key: "codigo", label: "codigo", sortable: true },
                { key: "artNombre", label: "NOMBRE", sortable: true },
                { key: "catNombre", label: "CATEGORIA", sortable: true },
                { key: "marNombre", label: "MARCA", sortable: true },
                { key: "uniNombre", label: "UNIDAD", sortable: true },
                { key: "artCosto", label: "COSTO", sortable: true },
                { key: "artPrecioVenta", label: "PRECIO VENTA", sortable: true },
                { key: "artCantidad", label: "CANTIDAD", sortable: true },
                { key: "Acción", sortable: false },
            ],

            show: false,
            variant: "dark",
            opacity: 0.85,
            blur: "2px",
            // selected: [],
        };
    },

    // computed: {
    //   sortOptions() {
    //     // Create an options list from our fields
    //     return this.fields
    //       .filter((f) => f.sortable)
    //       .map((f) => {
    //         return { text: f.label, value: f.key };
    //       });
    //   },
    // },
    mounted() {
      this.listArticulo();


    },
    methods: {
        clickAccion(item, accion) {


            if (accion === "guardar") {
                this.$store.dispatch('app/cambiarTipoAccion', { tipo: accion, variant: 'success', icono: 'SaveIcon', texto: 'Guardar', Bclass: '' })
            }
            if (accion === "editar") {

                this.$store.dispatch('app/cambiaId', item["artId"])
                this.$store.dispatch('app/cambiarTipoAccion', { tipo: accion, variant: 'primary', icono: 'SaveIcon', texto: 'Modificar', Bclass: '' })

                this.$refs["frm-articulo"].show();
            }
            if (accion === "ver") {
                this.$store.dispatch('app/cambiaId', item["artId"])
                this.$store.dispatch('app/cambiarTipoAccion', { tipo: accion, variant: 'success', icono: 'SaveIcon', texto: 'Guardar', Bclass: 'd-none' })

                this.$refs["frm-articulo"].show();
            }
            if (accion === "eliminar") {
                this.ControlaEliminar(item)
            }
        },
        UsuarioAlerta(variant) {

            if (variant === "success") {
                this.$swal({
                    title: "Buen Trabajo",
                    text: "Operacion Exitosa",
                    icon: variant,
                    customClass: {
                        confirmButton: "btn btn-success",
                    },
                    showClass: {
                        popup: "animate__animated animate__bounceIn",
                    },
                    buttonsStyling: true,
                });
            } else {
                this.$swal({
                    title: "¡Error!",
                    text: "No se Logro Realizar la Operacíon",
                    icon: variant,
                    customClass: {
                        confirmButton: "btn btn-danger",
                    },
                    showClass: {
                        popup: "animate__animated animate__tada",
                    },
                    buttonsStyling: true,
                });
            }
        },
        ControlaEliminar(item) {
            this.boxTwo = "";
            this.$bvModal
                .msgBoxConfirm(
                    "El Articulo  " + " : " + item["artId"] + " Serán Eliminados",
                    {
                        title: "Advertencia",
                        size: "sm",
                        okVariant: "success",
                        okTitle: "Continuar",
                        cancelTitle: "Cancelar",
                        cancelVariant: "danger",
                        hideHeaderClose: true,
                        centered: true,
                    }
                )
                .then((value) => {
                    this.boxTwo = value;
                    if (value === true) {
                        this.eliminar(item);
                    }
                });
        },
        eliminar(item) {
            let me = this;
            const axios = require("axios").default;
            const params = new URLSearchParams();
            params.append('artId', item["artId"]);
            var url = "api/auth/eliminarArticulo";
            me.isBusy = true;
            axios
                .post(url, params)
                .then(function (response) {

                    if (response.status == 201) {
                        me.UsuarioAlerta("success");
                        me.listArticulo()
                        me.isBusy = false;
                    } else {
                        me.UsuarioAlerta("danger");
                    }

                })
                .catch((e) => {
                    me.UsuarioAlerta("danger");
                    console.log("danger", "No se Realizó la Operación: " + e);
                });
        },
        onFiltered(filteredItems) {
            // Trigger pagination to update the number of buttons/pages due to filtering
            this.totalRows = filteredItems.length;
        },
        limpiarVariables() {
            let me = this;
            me.listArticulo();
            me.shows = false;
            me.loaded = false;
            me.Loading = "";
            me.filter = null;
            me.filterOn = [];
            me.gestion = 0;
            me.mes = 0;
            me.iExiste = 0;
            me.estado = "";
        },
        makeToast(variant) {
            let me = this;
            if (variant === "success") {
                this.$bvToast.toast("Registro Exitoso ", {
                    title: `HR Analytics`,
                    variant,
                    solid: true,
                    appendToast: true,
                });
            } else {
                this.$bvToast.toast("Error en el Registro ", {
                    title: `HR Analytics`,
                    variant,
                    solid: true,
                    appendToast: true,
                });
            }
        },

        listArticulo() {
            let me = this;
            const axios = require("axios").default;
            const params = new URLSearchParams();
            // params.append('email', me.email);
            me.metadataListado = [];
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
                            artId: resp[i].artId,
                            artNombre: resp[i].artNombre,
                            artPrecioVenta: resp[i].artPrecioVenta,
                            artFoto: 'data:image/png;base64,' + resp[i].artFoto
                        });
                    }
                    me.metadataListado = lista;
                    me.isBusy = false;
                    me.loaded = true;
                })
                .catch((e) => {
                    alert("error al obtener los datos Lista Articulo " + e);
                });
        },
        onContext(ctx) {
            // The date formatted in the locale, or the `label-no - date - selected` string
            this.formatted = ctx.selectedFormatted;
            // The following will be an empty string until a valid date is entered
            this.mesReporte = ctx.selectedYMD;
        },
    },
};


function MesActual(fecha, formato) {
    // 2022-09-02 21:51:48.000000"
    const map = {
        dd: fecha.getDate(),
        mm: fecha.getMonth() + 1,
        yy: fecha.getFullYear().toString(),
        yyyy: fecha.getFullYear(),
        HH: fecha.getHours(),
        MM: fecha.getMinutes(),
        SS: fecha.getSeconds(),
    };
    // HH:MM:SS
    return formato.replace(/yy|mm|dd|HH|MM|SS/gi, (matched) => map[matched]);
}
</script>
<style scoped></style>
<style lang="scss" >
@import '~@resources/scss/vue/libs/vue-select.scss';
@import "~@resources/scss/base/pages/app-ecommerce.scss";

.custom-badge {
    width: 30px;
    height: 15px;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ecommerce-card {
    &:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 25px 0 rgba($black, 0.25);
    }


}
</style>
        