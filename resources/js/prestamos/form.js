class PrestamoForm {
    constructor() {
        this.tipo = document.getElementById('tipoPrestamo');
        this.cardGarantes = document.getElementById('cardGarantes');
        this.garantesContainer = document.getElementById('garantesContainer');
        this.solicitante = null;
        this.garantes = [];
       // this.garantesManager = new GarantesManager();
        this.monto = document.getElementById('monto');
        this.plazo = document.getElementById('plazo');

        this.maxMonto = document.getElementById('maxMonto');
        this.maxPlazo = document.getElementById('maxPlazo');
        this.asiento = document.getElementById('asiento');
        this.rEstado = document.getElementById('rEstado');
        this.rTipo = document.getElementById('rTipo');
        this.rMoneda = document.getElementById('rMoneda');
        this.rInteres = document.getElementById('rInteres');
        this.rGarantes = document.getElementById('rGarantes');
        this.rMontoMax = document.getElementById('rMontoMax');
        this.rPlazoMax = document.getElementById('rPlazoMax');
        this.rMonto=document.getElementById('rMonto');
        this.rPlazo=document.getElementById('rPlazo');
        this.rGarantesActuales=document.getElementById('rGarantesActuales');
        this.rInteresCalculado = document.getElementById('rInteresCalculado');
        this.rItf = document.getElementById('rItf');
        this.rPapeleria = document.getElementById('rPapeleria');
        this.rLiquido = document.getElementById('rLiquido');
        this.rCuota = document.getElementById('rCuota');
        this.urlValidar = document.getElementById('urlValidarSolicitud').value;
        this.fechaPrestamo = document.getElementById('fechaPrestamo');
        this.cronogramaBody = document.getElementById('cronogramaBody');
        this.rTotalPagado = document.getElementById('rTotalPagado');
        this.validacionOK = true;
        this.modoEdicion = document.getElementById('modoEdicion') !== null;
        this.tipoOriginal = null;
        this.garante1Original = document.getElementById('garante1Original')?.value || '';
        this.garante2Original = document.getElementById('garante2Original')?.value || '';
       

        this.urlSimular = document.getElementById('urlSimular').value; 
        this.timerSimulacion = null;

        this.configPrestamo = {};
        this.init();
        
        
        // Restaurar el tipo de préstamo después de una validación
        if (this.tipo && this.tipo.value !== '') {
            this.cargarTipo();
        }

        if (this.modoEdicion && this.tipo) {
            this.tipoOriginal = this.tipo.value;
        }
       
                
    }

  
    init() {
       
        if (this.tipo) {
            this.tipo.addEventListener(
                'change',
                () => this.cargarTipo()
            );
        }

        document.addEventListener(
            'scas:selected',
            (e) => this.socioSeleccionado(e)
        );

        if (this.monto) {
            this.monto.addEventListener(
                'blur',
                () => this.validarMonto()
            );
            this.monto.addEventListener(
                'input',
                () => this.actualizarFinanciero()
            );
            this.fechaPrestamo.addEventListener(
                'blur',
                () => this.programarSimulacion()
            );
            this.fechaPrestamo.addEventListener(
                'change',
                () => this.programarSimulacion()
            );
        }

        if (this.plazo) {
            this.plazo.addEventListener(
                'blur',
                () => this.validarPlazo()
            );
             this.plazo.addEventListener(
                'input',
                () => this.actualizarFinanciero()
            );
        }
    

        if (this.asiento) {
            this.asiento.addEventListener(
                'blur',
                () => this.formatearAsiento()
            );
        }

        this.monto.addEventListener('input',()=>{
            this.programarSimulacion();
        });

        this.plazo.addEventListener('input',()=>{
            this.programarSimulacion();
        });

    }

    cargarTipo() {
        const opcion = this.tipo.options[this.tipo.selectedIndex];
        if (!opcion.value) {

            this.cardGarantes.classList.add('d-none');
            this.garantesContainer.innerHTML = '';

            return;
        }

        const cantidad = parseInt(opcion.dataset.garante);

        this.crearGarantes(cantidad);
        this.restaurarGarantes();
        this.configPrestamo = {
            id: parseInt(opcion.value),
            montoMax: parseFloat(opcion.dataset.monto),
            plazoMax: parseInt(opcion.dataset.plazo),
            interes: parseFloat(opcion.dataset.interes),
            moneda: opcion.dataset.moneda,
            itf: parseFloat(opcion.dataset.itf || 0),
            papeleria: parseFloat(opcion.dataset.papeleria || 0),
            minDefensa: parseFloat(opcion.dataset.mindefensa || 0)
        };
        this.actualizarResumen();
        if (this.maxMonto) {
            this.maxMonto.innerHTML =
                'Máximo permitido: ' +
                this.formatearMoneda(
                    this.configPrestamo.montoMax
                );
        }

        if (this.maxPlazo) {
            this.maxPlazo.innerHTML =
                'Máximo permitido: ' +
                this.configPrestamo.plazoMax +
                ' meses';
        } 
        this.programarSimulacion();
        this.validarPrestamoActual();
    }

    ////////////////////////////////////////FUNCION PARA EDICION
    esTipoOriginal() {
        if (!this.modoEdicion) {
            return false;
        }
        return this.tipo.value == this.tipoOriginal;
    }

    validarPrestamoActual() {
        if (!this.solicitante || !this.configPrestamo.id) {
            return;
        }

        if (this.esTipoOriginal()) {
            this.habilitarFormulario(true);
            return;
        }
        this.validarSolicitud();
    }

    inicializarEdicion() {
        if (!this.modoEdicion) {
            return;
        }
        this.actualizarFinanciero();
        this.programarSimulacion();
    }



    crearGarantes(cantidad) {
        this.garantesContainer.innerHTML = '';
        if (cantidad <= 0) {
            this.cardGarantes.classList.add('d-none');
            return;
        }
        this.cardGarantes.classList.remove('d-none');
        for (let i = 1; i <= cantidad; i++) {
            this.garantesContainer.insertAdjacentHTML('beforeend', `
                <div class="col-md-6">
                    <label class="form-label">
                        Garante ${i}
                    </label>
                    <div class="scas-papeleta">
                        <input type="hidden"
                            name="id_garante${i}"
                            class="scas-id">
                        <input type="text" class="form-control scas-papeleta-input" autocomplete="off" spellcheck="false"
                            placeholder="Ingrese la papeleta..."
                            data-url="/socios/buscar">
                        <div class="list-group position-absolute w-100 shadow scas-resultados"
                            style="display:none;z-index:1055;">
                        </div>
                        <div class="form-text scas-nombre text-primary fw-semibold"></div>
                    </div>
                </div>
            `);
        }
        this.garantesContainer
            .querySelectorAll('.scas-papeleta')
            .forEach(c => new PapeletaSearch(c));

    }
    socioSeleccionado(e) {
        const contenedor = e.target;
        const socio = e.detail;
        const hidden = contenedor.querySelector('.scas-id');
        const nombreCampo = hidden.getAttribute('name');

        if (nombreCampo === 'id_socio') {

           this.solicitante = socio;
            this.actualizarSolicitante();

        } else {
            this.garantes = this.garantes.filter(
                    g => g.campo !== nombreCampo
                );

                this.garantes.push({
                    campo: nombreCampo,
                    socio: socio
                });
        }
        this.validarGarantes();
        this.actualizarGarantes();
        this.validarPrestamoActual();
        this.inicializarEdicion();
    }

    validarGarantes() {
        if (!this.solicitante) return;
        let ids = [];
        for (let garante of this.garantes) {
            // solicitante no puede ser garante
            if (garante.socio.id === this.solicitante.id) {
                ScasNotifier.show('El solicitante no puede ser garante.','danger');
                this.limpiarCampo(garante.campo);
                return;
            }

            // garantes repetidos

            if (ids.includes(garante.socio.id)) {
                ScasNotifier.show('No puede repetir el mismo garante.','warning');
                this.limpiarCampo(garante.campo);
                return;
            }
            ids.push(garante.socio.id);
        }
    }

    limpiarCampo(nombre) {
        const hidden =
            document.querySelector(
                'input[name="'+nombre+'"]'
            );

        if (!hidden) return;
        const contenedor =
            hidden.closest('.scas-papeleta');

        hidden.value = '';
        contenedor.querySelector(
            '.scas-papeleta-input'
        ).value = '';

        contenedor.querySelector(
            '.scas-nombre'
        ).innerHTML = '';

        this.garantes =
            this.garantes.filter(
                g => g.campo !== nombre
            );

    }

    validarMonto(){
        let monto=parseFloat(this.monto.value||0);
        if(monto>this.configPrestamo.montoMax){
            this.monto.classList.add('is-invalid');
            ScasNotifier.show(
                'El monto excede el máximo permitido.',
                'danger'
            );
            return;
        }
        this.monto.classList.remove('is-invalid');
    }

    validarPlazo(){
        let plazo=parseInt(this.plazo.value||0);
        if(plazo>this.configPrestamo.plazoMax){
            this.plazo.classList.add('is-invalid');
            ScasNotifier.show(
                'El plazo excede el máximo permitido.',
                'danger'
            );
            return;
        }
        this.plazo.classList.remove('is-invalid');
    }

    formatearMoneda(monto) {
        const simbolo =
            this.configPrestamo.moneda === 'SU'
                ? '$us'
                : 'Bs.';
        return simbolo + ' ' +
            Number(monto).toLocaleString(
                'es-BO',
                {
                    minimumFractionDigits:2,
                    maximumFractionDigits:2
                }
            );
    }

    formatearAsiento() {
        let valor = this.asiento.value
            .replace(/[^0-9]/g,'');

        this.asiento.value = valor;
    }

    actualizarResumen(datos = null){
    if(!this.configPrestamo) return;

        const opcion =
            this.tipo.options[this.tipo.selectedIndex];

        this.rTipo.textContent =
            opcion.text;

        this.rMoneda.textContent =
            this.configPrestamo.moneda === 'SU'
                ? 'Dólares ($us)'
                : 'Bolivianos (Bs.)';

        this.rInteres.textContent =
            this.configPrestamo.interes + ' %';

        this.rGarantes.textContent =
            opcion.dataset.garante;

        this.rMontoMax.textContent =
            this.formatearMoneda(
                this.configPrestamo.montoMax
            );

        this.rPlazoMax.textContent =
            this.configPrestamo.plazoMax +
            ' meses';

        if(datos){

           if(this.rCuota){
                this.rCuota.textContent =
                    this.formatearMoneda(datos.cuota);
            }

            if(this.rInteresCalculado){
                this.rInteresCalculado.textContent =
                    this.formatearMoneda(datos.interesTotal);
            }

            if(this.rTotalPagado){
                this.rTotalPagado.textContent =
                    this.formatearMoneda(datos.totalPagado);
            }

        }

    }

    actualizarSolicitante(){
        let contenedor =
            document.getElementById('rSolicitante');

        if(!contenedor) return;

        contenedor.innerHTML = `
            <div class="fw-bold">
                ${this.solicitante.nombre}
            </div>

            <small class="text-muted">
                Papeleta:
                ${this.solicitante.papeleta}
            </small>
        `;

    }
    actualizarFinanciero(){
        if(this.rMonto){

            this.rMonto.textContent=
                this.monto.value
                ? this.formatearMoneda(this.monto.value)
                : '-';
        }
        if(this.rPlazo){
            this.rPlazo.textContent=
                this.plazo.value
                ? this.plazo.value+' meses'
                : '-';
        }
        this.calcularResumenFinanciero();
    }
    actualizarGarantes(){
        if(!this.rGarantesActuales)
            return;

        this.rGarantesActuales.textContent=
            this.garantes.length+
            ' seleccionado(s)';

    }

    calcularResumenFinanciero(){

        if(!this.configPrestamo)
            return;

        const monto = parseFloat(this.monto.value || 0);

        const plazo = parseInt(this.plazo.value || 0);

        if(monto<=0 || plazo<=0){

            this.rInteresCalculado.textContent='-';
            this.rItf.textContent='-';
            this.rPapeleria.textContent='-';
            this.rLiquido.textContent='-';
            this.rCuota.textContent='-';

            return;

        }

        const interes =
            monto *
            (this.configPrestamo.interes/100);

        const itf =
            monto *
            (this.configPrestamo.itf/100);

        const papeleria =
            monto *
            (this.configPrestamo.papeleria/100);

        const liquido =
            monto -
            itf -
            papeleria;

        const cuota =
            (monto + interes) /
            plazo;

        this.rInteresCalculado.textContent =
            this.formatearMoneda(interes);

        this.rItf.textContent =
            this.formatearMoneda(itf);

        this.rPapeleria.textContent =
            this.formatearMoneda(papeleria);

        this.rLiquido.textContent =
            this.formatearMoneda(liquido);

        this.rCuota.textContent =
            this.formatearMoneda(cuota);
    }

    async validarSolicitud(){
        if(!this.solicitante) return;
        if(!this.configPrestamo.id) return;
        try{
            const response = await fetch(this.urlValidar,{
                method:'POST',
                headers:{
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN':
                        document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content
                },

                body:JSON.stringify({
                    id_socio:this.solicitante.id,
                    id_tasa:this.configPrestamo.id
                })
            });

            if (!response.ok) {
                console.error(await response.text());
                return;
            }

            const datos = await response.json();
            this.validacionOK = datos.ok;
            if(datos.ok){
                this.habilitarFormulario(true);
                return;
            }

            this.habilitarFormulario(false);
            ScasNotifier.show(
                datos.mensaje,
                'warning'
            );
           
        }
        catch(error){
            console.error(error);
        }
    }

    habilitarFormulario(habilitar){
        this.monto.disabled = !habilitar;
        this.plazo.disabled = !habilitar;
        this.asiento.disabled = !habilitar;
        this.btnGuardar = document.getElementById('btnGuardarPrestamo');

        document.querySelectorAll('#garantesContainer input')
            .forEach(i=>{
                i.disabled = !habilitar;
            });
        if (this.btnGuardar) {
            this.btnGuardar.disabled = !habilitar;
        }
    }

    programarSimulacion(){
        clearTimeout(this.timerSimulacion);
        this.timerSimulacion = setTimeout(()=>{
            this.simular();
        },400);
    }
    async simular(){
        if(!this.validacionOK)
            return;
        if(!this.monto.value)
            return;
        if(!this.plazo.value)
            return;
        if(!this.configPrestamo)
            return;
        const response = await fetch(this.urlSimular,{
            method:'POST',
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':
                    document
                    .querySelector(
                        'meta[name="csrf-token"]'
                    ).content
            },
            body:JSON.stringify({
                monto:this.monto.value,
                plazo:this.plazo.value,
                porcentaje:
                    this.configPrestamo.interes,
                fecha:
                    this.fechaPrestamo.value,
                tipo_moneda:
                    this.configPrestamo.moneda,
                tipo:
                    this.configPrestamo.id,
                itf: this.configPrestamo.itf,
                papeleria:this.configPrestamo.papeleria,
                min_defensa: this.configPrestamo.minDefensa,
            })
        });
        const datos =await response.json();

        this.cronograma = datos.cronograma;
        document.getElementById('cronograma').value = JSON.stringify(this.cronograma);
       //console.log(datos.cronograma[0]);
       
        this.mostrarCronograma(datos);
        this.actualizarResumen(datos);
    }
    mostrarCronograma(datos){

        this.cronogramaBody.innerHTML='';
        datos.cronograma.forEach(cuota=>{
            this.cronogramaBody.innerHTML += `
            <tr>
                <td>${cuota.numero}</td>
                <td>${cuota.fecha}</td>
                <td class="text-end">
                    ${Number(cuota.capital).toFixed(2)}
                </td>
                <td class="text-end">
                    ${Number(cuota.interes).toFixed(2)}
                </td>
                <td class="text-end fw-bold">
                    ${Number(cuota.cuota).toFixed(2)}
                </td>
                <td class="text-end">
                    ${Number(cuota.saldo).toFixed(2)}
                </td>
            </tr>`;
        });
    }

    restaurarGarantes() {
        if (!this.modoEdicion) {
            return;
        }

        const ids = [
            this.garante1Original,
            this.garante2Original
        ];

        ids.forEach((id, index) => {

            if (!id) return;
            const hidden = document.querySelector(
                `input[name="id_garante${index + 1}"]`
            );

            if (!hidden) return;
            hidden.value = id;
            const contenedor = hidden.closest('.scas-papeleta');
            if (contenedor) {
                new PapeletaSearch(contenedor).restaurar();
            }
        });
    }

}

document.addEventListener('DOMContentLoaded', () => {

    const formulario = document.getElementById('frmPrestamo');

    if (!formulario) {
        return;
    }
    new PrestamoForm();
});

