<template>
    <div :class="{'dark-theme': darkTheme}"  id="de" class="nota-de-venta">
        <h1 class="titulo">Nota de Venta</h1>
        <p class="fecha">{{ fecha }}</p>
        <div class="cliente">
            <p><strong>Cliente:</strong> {{ cliente }}</p>
        </div>
        <table class="tabla">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in items" :key="item.id">
                    <td>{{ item.producto }}</td>
                    <td>{{ item.cantidad }}</td>
                    <td>{{ item.precioUnitario }}</td>
                    <td>{{ item.total }}</td>
                </tr>
            </tbody>
        </table>
        <div class="total">
            <p><strong>Total:</strong> {{ totalVenta }}</p>
        </div>
        <button @click="imprimirNota" class="btn-imprimir no-imprimir">Imprimir</button>
    </div>
</template>
  
<script>
import printJS from 'print-js';
export default {
    data() {
        return {
            fecha: "2023-10-30",
            cliente: "Cliente de ejemplo",
            items: [
                { id: 1, producto: "Producto A", cantidad: 2, precioUnitario: 10, total: 20 },
                { id: 2, producto: "Producto B", cantidad: 3, precioUnitario: 15, total: 45 },
                { id: 3, producto: "Producto C", cantidad: 1, precioUnitario: 30, total: 30 },
            ],
        };
    },
    computed: {
        totalVenta() {
            return this.items.reduce((total, item) => total + item.total, 0);
        },
    },
    methods: {
        imprimirNota() {
            // Aquí puedes agregar el código para imprimir la nota de venta
            printJS({
                printable: 'de',
                type: 'html',
                importCSS: true,  // Importar estilos CSS
                printContainer: true,
                scanStyles: true, 
                
             
            });

        },
    },
};
</script>
  
<style scoped>
/* Estilos para el tema claro (light) */
.nota-de-venta {
  font-family: Arial, sans-serif;
  background-color: #f7f8f9;
  padding: 20px;
  margin: 20px;
  border: 1px solid #040000;
  box-shadow: 2px 2px 5px #131313;
  max-width: 400px;
  margin: 0 auto;
  color: #000; /* Cambio de color de letras en el tema claro */
}

.titulo {
  text-align: center;
  font-size: 24px;
  margin-bottom: 20px;
}

.fecha {
  text-align: right;
  margin: 0;
}

.cliente {
  margin-top: 20px;
}

.tabla {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

th, td {
  border: 1px solid #2f0404;
  text-align: center;
  padding: 8px;
}

th {
  background-color: #e88888;
}

.total {
  text-align: right;
  margin-top: 20px;
  font-size: 18px;
  font-weight: bold;
}

.btn-imprimir {
  display: block;
  margin: 20px auto;
  background-color: #007bff;
  color: #fff;
  border: none;
  padding: 10px 20px;
  cursor: pointer;
}

/* Estilos para el tema oscuro (dark) */
.dark-theme .nota-de-venta {
  background-color: #333;
  border: 1px solid #777;
  box-shadow: 2px 2px 5px #888;
  color: #000000; /* Cambio de color de letras en el tema oscuro */
}

.dark-theme .titulo {
  color: #fff;
}

.dark-theme .fecha {
  color: #fff;
}

.dark-theme .cliente {
  color: #fff;
}

.dark-theme .tabla {
  border: 1px solid #aaa;
  color: #fff; /* Cambio de color de letras en el tema oscuro */
}

.dark-theme th, .dark-theme td {
  border: 1px solid #aaa;
  color: #fff;
}

.dark-theme .total {
  color: #fff;
}

.dark-theme .btn-imprimir {
  background-color: #fff;
  color: #000;
}

@media print {
  .no-imprimir {
    display: none;
  }
}
</style>
