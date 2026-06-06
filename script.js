function getNowLabel() {
  const d = new Date();
  const hariNames = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const bulanNames = ['Januari','Februari','Maret','April','Mei','Juni',
                      'Juli','Agustus','September','Oktober','November','Desember'];
  
  const hari = hariNames[d.getDay()];
  const tgl  = d.getDate();
  const bln  = bulanNames[d.getMonth()];
  const thn  = d.getFullYear();
  
  let m = d.getMinutes();
  let s = d.getSeconds();
  if (m < 10) m = '0' + m;
  if (s < 10) s = '0' + s;
  
  return `${hari}, ${tgl} ${bln} ${thn} — ${d.getHours()}:${m}:${s} WIB`;
}

function switchTab(tabId) {
  document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.tab-section').forEach(el => el.classList.remove('active'));

  const activeSection = document.getElementById('tab-' + tabId);
  if (activeSection) activeSection.classList.add('active');

  const eventLink = event ? event.currentTarget : null;
  if (eventLink) {
    eventLink.classList.add('active');
  } else {
    document.querySelectorAll('.nav-menu a').forEach(a => {
      if (a.getAttribute('onclick').includes(tabId)) a.classList.add('active');
    });
  }

  if (tabId === 'dashboard' || tabId === 'history' || tabId === 'rewards') {
    loadDashboardAndHistory();
  }
}

function loadDashboardAndHistory() {
  fetch("api/get_dashboard_data.php")
    .then(res => res.json())
    .then(data => {
      document.getElementById('dash-total-berat').innerHTML = `${parseFloat(data.ringkasan.total_berat).toFixed(2)} <span class="stat-unit">kg</span>`;
      document.getElementById('dash-poin').textContent = parseInt(data.ringkasan.current_poin).toLocaleString('id-ID');
      document.getElementById('dash-co2').innerHTML = `${parseFloat(data.ringkasan.total_co2).toFixed(2)} <span class="stat-unit">kg</span>`;
      document.getElementById('dash-pohon').innerHTML = `${parseFloat(data.ringkasan.total_pohon).toFixed(2)} <span class="stat-unit">pohon/thn</span>`;
      document.getElementById('rw-current-poin').textContent = parseInt(data.ringkasan.current_poin).toLocaleString('id-ID');

      const tbodyDash = document.getElementById('dash-table-body');
      tbodyDash.innerHTML = "";
      data.breakdown.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <strong><td>${row.kategori}</td></strong>
          <td>${parseFloat(row.berat_kategori).toFixed(2)} kg</td>
          <td><span style="color:var(--coral); font-weight:600;">${row.koefisien}</span></td>
          <td><strong>${parseFloat(row.co2_kategori).toFixed(2)} kg CO₂</strong></td>
          <td><span style="color:#28a745; font-weight:600;">🌳 ${parseFloat(row.pohon_kategori).toFixed(2)}</span></td>
        `;
        tbodyDash.appendChild(tr);
      });

      const tbodyHist = document.getElementById('history-table-body');
      tbodyHist.innerHTML = "";
      if (data.history.length === 0) {
        tbodyHist.innerHTML = `<tr><td colspan="6" class="text-muted" style="text-align:center;">Belum ada log transaksi terekam.</td></tr>`;
      } else {
        data.history.forEach(row => {
          const tr = document.createElement('tr');
          
          let badgeClass = "badge-amber";
          if(row.status === 'Selesai' || row.status === 'Berhasil') badgeClass = "badge-green";
          if(row.status === 'Batal') badgeClass = "badge-coral";
          if(row.status === 'Diproses') badgeClass = "badge-blue";

          tr.innerHTML = `
            <td>${row.tanggal}</td>
            <td style="font-family:monospace; font-weight:700;">${row.kode_trx}</td>
            <td><strong>${row.tipe}</strong></td>
            <td class="text-muted">${row.meta_desc}</td>
            <td><strong>${row.value_display}</strong></td>
            <td><span class="status-badge ${badgeClass}">${row.status}</span></td>
          `;
          tbodyHist.appendChild(tr);
        });
      }
    })
    .catch(err => console.error("Error load dashboard:", err));

  fetch("api/get_active_pickup.php")
    .then(res => res.json())
    .then(res => {
      const ph = document.getElementById('pu-status-placeholder');
      const card = document.getElementById('pu-status-card');
      if (res.status === 'success' && res.data) {
        ph.style.display = 'none';
        card.style.display = 'block';
        document.getElementById('pu-status-id').textContent = res.data.kode_pickup;
        document.getElementById('pu-status-text').textContent = res.data.status;
        document.getElementById('pu-status-jadwal').textContent = res.data.hari_jadwal + " (" + res.data.jam_jadwal + ")";
        document.getElementById('pu-status-alamat').textContent = res.data.alamat;
        document.getElementById('pu-status-notes').textContent = res.data.catatan || '-';
      } else {
        ph.style.display = 'block';
        card.style.display = 'none';
      }
    });
}

function handleDropoffSubmit(e) {
  e.preventDefault();
  const tps = document.getElementById('do-tps').value;
  const kat = document.getElementById('do-kategori').value;
  const est = document.getElementById('do-perkiraan').value;

  const formData = new FormData();
  formData.append('tps', tps);
  formData.append('kategori', kat);
  formData.append('perkiraan_berat', est);

  fetch("api/create_dropoff.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.json())
  .then(res => {
    if (res.status === 'success') {
      alert("Tiket Drop-off Berhasil Dibuat!");
      document.getElementById('do-qr-placeholder').style.display = 'none';
      const qrCont = document.getElementById('do-qr-container');
      qrCont.style.display = 'inline-block';
      
      document.getElementById('do-qr-code-text').textContent = res.kode_dropoff;
      document.getElementById('do-qr-meta').innerHTML = `
        <strong>Lokasi:</strong> ${tps}<br>
        <strong>Kategori:</strong> ${kat}<br>
        <strong>Est. Berat:</strong> ${est} kg
      `;
      document.getElementById('form-dropoff').reset();
      document.getElementById('do-datetime-display').textContent = getNowLabel();
    } else {
      alert("Gagal: " + res.message);
    }
  })
  .catch(err => console.error(err));
}

function handlePickupSubmit(e) {
  e.preventDefault();
  const hari = document.getElementById('pu-date').value;
  const jam  = document.getElementById('pu-time').value;
  const alm  = document.getElementById('pu-alamat').value;
  const cat  = document.getElementById('pu-catatan').value;

  const formData = new FormData();
  formData.append('hari_jadwal', hari);
  formData.append('jam_jadwal', jam);
  formData.append('alamat', alm);
  formData.append('catatan', cat);

  fetch("api/create_pickup.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.json())
  .then(res => {
    if (res.status === 'success') {
      alert("Booking Jadwal Kurir Sukses!");
      document.getElementById('form-pickup').reset();
      loadDashboardAndHistory();
    } else {
      alert("Gagal: " + res.message);
    }
  })
  .catch(err => console.error(err));
}

function handleRedeemSubmit(e) {
  e.preventDefault();
  const wallet = document.getElementById('rw-wallet').value;
  const phone  = document.getElementById('rw-phone').value;
  const jumlah = document.getElementById('rw-jumlah').value;

  if (parseInt(jumlah) < 5000) {
    alert("Minimal penukaran adalah 5.000 Poin!");
    return;
  }

  const formData = new FormData();
  formData.append('wallet_provider', wallet);
  formData.append('nomor_akun', phone);
  formData.append('jumlah_poin', jumlah);

  fetch("api/create_redeem.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.json())
  .then(res => {
    if (res.status === 'success') {
      alert("Penukaran Berhasil! Saldo sedang dikirim ke " + wallet);
      document.getElementById('form-redeem').reset();
      loadDashboardAndHistory();
    } else {
      alert("Gagal mencairkan poin: " + res.message);
    }
  })
  .catch(err => console.error(err));
}


function fillDateDropdowns() {
  const hariNames  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const bulanNames = ['Januari','Februari','Maret','April','Mei','Juni',
                      'Juli','Agustus','September','Oktober','November','Desember'];
  const options = [];
  const today   = new Date();
  today.setHours(0, 0, 0, 0);
  for (let i = 0; i <= 7; i++) {
    const d     = new Date(today);
    d.setDate(today.getDate() + i);
    const label = hariNames[d.getDay()] + ', ' + d.getDate() + ' ' +
                  bulanNames[d.getMonth()] + ' ' + d.getFullYear();
    options.push(`<option value="${label}">${i === 0 ? 'Hari ini — ' + label : label}</option>`);
  }
  const puDate = document.getElementById('pu-date');
  if (puDate) puDate.innerHTML = options.join('');

  const dispEl = document.getElementById('do-datetime-display');
  if (dispEl) dispEl.textContent = getNowLabel();
}

window.onload = function() {
  fillDateDropdowns();   
  fetch("api/check_session.php")
    .then(response => response.json())
    .then(data => {
      if (data.logged_in) {
        document.getElementById('sb-username').textContent = "👋 " + data.username;
        loadDashboardAndHistory();
      } else {
        window.location.href = "login.html";
      }
    })
    .catch(err => {
      console.error("Session check error:", err);
    });
};