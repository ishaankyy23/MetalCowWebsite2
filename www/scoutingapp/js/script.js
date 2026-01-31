// --- 1. INITIALIZATION ---
// Configuration is loaded from config.js
// Do NOT put API keys directly in this file - use config.js instead!

const scoutDB = window.supabase.createClient(SUPABASE_CONFIG.SB_URL, SUPABASE_CONFIG.SB_KEY);

// --- CUSTOM MODAL SYSTEM ---
function _createAppModal() {
    if (document.getElementById('app-modal-overlay')) return;
    const overlay = document.createElement('div');
    overlay.id = 'app-modal-overlay';
    overlay.innerHTML = `
        <div id="app-modal" role="dialog" aria-modal="true">
            <div id="app-modal-header"></div>
            <div id="app-modal-body"></div>
            <div id="app-modal-input-container" style="display:none; margin-bottom:12px;">
                <input id="app-modal-input" type="text" />
            </div>
            <div id="app-modal-footer">
                <button id="app-modal-cancel">Cancel</button>
                <button id="app-modal-ok">OK</button>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);

    overlay.querySelector('#app-modal-ok').addEventListener('click', () => {
        overlay.classList.remove('show');
        const ev = new CustomEvent('appModalClosed');
        overlay.dispatchEvent(ev);
    });
    overlay.querySelector('#app-modal-cancel').addEventListener('click', () => {
        overlay.classList.remove('show');
        const ev = new CustomEvent('appModalClosed');
        overlay.dispatchEvent(ev);
    });
}

function showModal(message, title) {
    _createAppModal();
    const overlay = document.getElementById('app-modal-overlay');
    overlay.querySelector('#app-modal-header').textContent = title || '';
    overlay.querySelector('#app-modal-body').textContent = message || '';

    return new Promise(resolve => {
        const onClose = () => {
            overlay.removeEventListener('appModalClosed', onClose);
            resolve();
        };
        overlay.addEventListener('appModalClosed', onClose);
        overlay.classList.add('show');
        setTimeout(() => overlay.querySelector('#app-modal-ok').focus(), 50);
    });
}

function showConfirm(message, title) {
    _createAppModal();
    const overlay = document.getElementById('app-modal-overlay');
    const ok = overlay.querySelector('#app-modal-ok');
    const cancel = overlay.querySelector('#app-modal-cancel');
    const inputContainer = overlay.querySelector('#app-modal-input-container');

    overlay.querySelector('#app-modal-header').textContent = title || '';
    overlay.querySelector('#app-modal-body').textContent = message || '';
    inputContainer.style.display = 'none';

    return new Promise(resolve => {
        const onOk = () => { cleanup(); overlay.classList.remove('show'); resolve(true); };
        const onCancel = () => { cleanup(); overlay.classList.remove('show'); resolve(false); };
        const cleanup = () => { ok.removeEventListener('click', onOk); cancel.removeEventListener('click', onCancel); };
        ok.addEventListener('click', onOk);
        cancel.addEventListener('click', onCancel);
        overlay.classList.add('show');
        setTimeout(() => ok.focus(), 50);
    });
}

function showPrompt(message, title, defaultValue = '') {
    _createAppModal();
    const overlay = document.getElementById('app-modal-overlay');
    const ok = overlay.querySelector('#app-modal-ok');
    const cancel = overlay.querySelector('#app-modal-cancel');
    const inputContainer = overlay.querySelector('#app-modal-input-container');
    const input = overlay.querySelector('#app-modal-input');

    overlay.querySelector('#app-modal-header').textContent = title || '';
    overlay.querySelector('#app-modal-body').textContent = message || '';
    input.value = defaultValue;
    inputContainer.style.display = 'block';

    return new Promise(resolve => {
        const onOk = () => { cleanup(); const v = input.value; overlay.classList.remove('show'); resolve(v); };
        const onCancel = () => { cleanup(); overlay.classList.remove('show'); resolve(null); };
        const cleanup = () => { ok.removeEventListener('click', onOk); cancel.removeEventListener('click', onCancel); };
        ok.addEventListener('click', onOk);
        cancel.addEventListener('click', onCancel);
        overlay.classList.add('show');
        setTimeout(() => input.focus(), 50);
    });
}

// --- 2. LOGIN ---
async function handleLogin() {
    const user = document.getElementById('username').value;
    const comp = document.getElementById('compName').value;

    if (!user || !comp) {
        await showModal("Please enter both Name and Competition!");
        return;
    }

    try {
        const { error } = await scoutDB.auth.signInAnonymously();
        if (error) {
            await showModal("Login Error: " + (error.message || JSON.stringify(error)));
            return;
        }

        sessionStorage.setItem('scoutName', user);
        sessionStorage.setItem('activeComp', comp);
        await showModal("Logged in as " + user);
        window.location.href = "pit.html"; 
    } catch (err) {
        await showModal("Error: " + err.message);
    }
}

// --- 3. SEND DATA ---
async function sendData(event) {
    if (event) event.preventDefault(); 
    
    const scoutName = sessionStorage.getItem('scoutName');
    const activeComp = sessionStorage.getItem('activeComp');

    if (!scoutName) {
        await showModal("Session missing. Please login again.");
        window.location.href = "login.html";
        return;
    }

    const form = event.target;
    const formData = new FormData(form);
    const allInputs = Object.fromEntries(formData.entries());

    form.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        allInputs[cb.name] = cb.checked;
    });

    const type = document.querySelector('h2').innerText.includes("Field") ? "Field" : "Pit";

    try {
        const { error } = await scoutDB
            .from('scouting_entries')
            .insert([{
                comp_name: activeComp,
                scout_name: scoutName,
                form_type: type,
                team_number: parseInt(allInputs.teamNumber),
                match_number: allInputs.matchNumber ? parseInt(allInputs.matchNumber) : null,
                details: allInputs 
            }]);

        if (error) throw error;

        await showModal("Data Saved Successfully!");
        form.reset();
    } catch (err) {
        console.error("Save error:", err);
        await showModal("Error: " + err.message);
    }
}

// --- 4. LOAD DATA ---
async function loadTableRows() {
    const tableBody = document.getElementById('tableBody');
    if (!tableBody) return;

    const { data, error } = await scoutDB
        .from('scouting_entries')
        .select('*')
        .order('created_at', { ascending: false });

    if (error) return;

    tableBody.innerHTML = data.map(row => `
        <tr>
            <td>${row.form_type}</td>
            <td>${row.team_number}</td>
            <td>${row.comp_name}</td>
            <td>${row.scout_name}</td>
            <td>${row.details.status || row.details.drivetrain || 'N/A'}</td>
            <td>${row.details.matchNotes || row.details.autos || 'N/A'}</td>
        </tr>
    `).join('');
}

// --- 5. ANALYTICS ---
async function renderCharts() {
    if (typeof Chart === 'undefined' || !document.getElementById('fuelChart')) {
        return; 
    }

    const { data: entries, error } = await scoutDB.from('scouting_entries').select('*');
    if (error) return console.error(error);

    const teams = {};

    entries.forEach(entry => {
        const t = entry.team_number;
        const d = entry.details || {};
        if (!teams[t]) teams[t] = { fuel: [], climbWins: 0, matchTotal: 0 };

        if (d.fuelpersec) teams[t].fuel.push(parseFloat(d.fuelpersec));

        if (entry.form_type === "Field") {
            teams[t].matchTotal++;
            if (d.matchClimb === "Success") teams[t].climbWins++;
        }
    });

    const teamLabels = Object.keys(teams);

    new Chart(document.getElementById('fuelChart'), {
        type: 'bar',
        data: {
            labels: teamLabels,
            datasets: [{
                label: 'Avg Fuel/Sec',
                data: teamLabels.map(t => teams[t].fuel.reduce((a,b)=>a+b,0) / teams[t].fuel.length || 0),
                backgroundColor: '#9c1c1c'
            }]
        }
    });

    new Chart(document.getElementById('climbChart'), {
        type: 'pie',
        data: {
            labels: teamLabels,
            datasets: [{
                label: 'Climb Success %',
                data: teamLabels.map(t => (teams[t].climbWins / teams[t].matchTotal * 100) || 0),
                backgroundColor: ['#f1c40f', '#e67e22', '#3498db', '#2ecc71']
            }]
        }
    });
}

// --- 6. DELETE DATA ---
async function clearAllData() {
    const confirmDelete = await showConfirm("Are you sure you want to PERMANENTLY delete all scouting data?");
    
    if (confirmDelete) {
        const verify = await showPrompt("Type 'DELETE ALL' to confirm:");
        
        if (verify === "DELETE ALL") {
            try {
                const { error } = await scoutDB
                    .from('scouting_entries')
                    .delete()
                    .gt('id', 0); 

                if (error) throw error;

                await showModal("Database cleared successfully!");
                location.reload();
                
            } catch (err) {
                console.error("Delete failed:", err);
                await showModal("Error: " + err.message);
            }
        }
    }
}

// Init
document.addEventListener('DOMContentLoaded', () => {
    loadTableRows();
    renderCharts();
});
