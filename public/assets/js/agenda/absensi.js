document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("btn-absen");

    const data = window.absensiHariIni;

    const jamMasuk = data?.jam_masuk;
    const jamKeluar = data?.jam_keluar;

    // ❌ belum absen sama sekali
    if (!jamMasuk) {
        return;
    }

    // ✅ sudah masuk → ubah jadi keluar
    if (jamMasuk && !jamKeluar) {
        btn.querySelector(".ag-label").innerHTML =
            `<i class="bi bi-box-arrow-right me-1"></i> Absen Keluar`;

        btn.setAttribute(
            "onclick",
            `agAbsen(this, 'filled', '${btn.dataset.url}', '${btn.dataset.urlUpdate}')`,
        );
    }

    // ✅ sudah selesai
    if (jamMasuk && jamKeluar) {
        setBtnDone(btn);
    }
});

// ── CLOCK ───────────────────────────────
const agDays = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
const agMonths = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember",
];

function agUpdateClock() {
    const now = new Date();
    const pad = (n) => String(n).padStart(2, "0");

    document.getElementById("ag-live-clock").innerText =
        `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;

    document.getElementById("ag-live-date").innerText =
        `${agDays[now.getDay()]}, ${now.getDate()} ${agMonths[now.getMonth()]} ${now.getFullYear()}`;
}

setInterval(agUpdateClock, 1000);
agUpdateClock();

function setBtnLoading(btn, isLoading) {
    const label = btn.querySelector(".ag-label");
    const loading = btn.querySelector(".ag-loading");

    if (isLoading) {
        btn.disabled = true;
        label.classList.add("d-none");
        loading.classList.remove("d-none");
    } else {
        btn.disabled = false;
        label.classList.remove("d-none");
        loading.classList.add("d-none");
    }
}

function setBtnDone(btn) {
    btn.disabled = true;
    btn.classList.remove("btn-primary");
    btn.classList.add("btn-secondary");

    btn.querySelector(".ag-label").innerHTML =
        `<i class="bi bi-check-circle me-1"></i> Selesai`;
}

function showToast(msg, isError = false) {
    const toast = document.getElementById("ag-toast");

    toast.classList.remove("d-none", "alert-success", "alert-danger");

    toast.classList.add(isError ? "alert-danger" : "alert-success");
    toast.innerText = msg;

    setTimeout(() => toast.classList.add("d-none"), 3000);
}

// ── ABSEN TOAST ─────────────────────────
function agAbsen(idUser, btn, jamMasuk, url, urlUpdate) {
    const now = new Date();
    const pad = (n) => String(n).padStart(2, "0");

    const payloadMasuk = {
        id_user: idUser,
        tanggal: `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`,
        domisili: "Yogyakarta",
        jam_masuk: `${pad(now.getHours())}:${pad(now.getMinutes())}`,
        status: now.getHours() >= 8.15 ? "Terlambat" : "Hadir",
    };

    const payloadKeluar = {
        id_user: idUser,
        jam_keluar: `${pad(now.getHours())}:${pad(now.getMinutes())}`,
    };

    setBtnLoading(btn, true);

    // ⛳ BELUM ABSEN MASUK
    if (!jamMasuk) {
        axios
            .post(url, payloadMasuk)
            .then((res) => {
                if (res.data.success) {
                    showToast(`Absen masuk berhasil`);

                    // ubah tombol jadi "Absen Keluar"
                    btn.querySelector(".ag-label").innerHTML =
                        `<i class="bi bi-box-arrow-right me-1"></i> Absen Keluar`;

                    // update state lokal
                    btn.setAttribute(
                        "onclick",
                        `agAbsen(${idUser}, this, 'filled', this.dataset.url, this.dataset.urlUpdate)`,
                    );
                }
            })
            .catch((err) => {
                showToast("Absen masuk gagal!", true);
                // showToast(err.response.data.message, true);
                console.log(err.response);
            })
            .finally(() => {
                setBtnLoading(btn, false);
            });
    }

    // ⛳ SUDAH MASUK → ABSEN KELUAR
    else {
        axios
            .put(urlUpdate, payloadKeluar)
            .then((res) => {
                if (res.data.success) {
                    showToast(`Absen keluar berhasil`);
                    setBtnDone(btn);
                }
            })
            .catch((err) => {
                showToast("Absen keluar gagal!", true);
                // showToast(err.response.data.message, true);
                console.log(err.response.data.message);
            })
            .finally(() => {
                setBtnLoading(btn, false);
            });
    }
}

// ── DATA ABSENSI ───────────────────────
const agEvent = window.agEvent || {};
let agCur = new Date();

// ── LIBUR API ──────────────────────────
let agLibur = {};

const agLembur = window.agLembur || {};
const agIzin = window.agIzin || {};

function isIzin(dateKey) {
    return agIzin.find((i) => {
        return dateKey >= i.tanggal_awal && dateKey <= i.tanggal_akhir;
    });
}

function getLembur(dateKey) {
    return agLembur.find((l) => l.tanggal_lembur === dateKey);
}
async function agFetchLibur(year) {
    try {
        const res = await fetch(
            `https://date.nager.at/api/v3/PublicHolidays/${year}/ID`,
        );
        const data = await res.json();

        agLibur = {};

        data.forEach((item) => {
            agLibur[item.date] = item.localName;
        });

        agRenderCal();
    } catch (err) {
        console.error("Gagal ambil hari libur:", err);
    }
}

// ── FORMAT TANGGAL ─────────────────────
function formatTanggalIndo(dateStr) {
    const d = new Date(dateStr);
    return `${d.getDate()} ${agMonths[d.getMonth()]} ${d.getFullYear()}`;
}

function getLembur(dateKey) {
    return agLembur.find((l) => l.tanggal_lembur === dateKey);
}

// ── CALENDAR ───────────────────────────
function agRenderCal() {
    const year = agCur.getFullYear();
    const month = agCur.getMonth();
    const today = new Date();

    document.getElementById("ag-cal-header").innerText =
        `${agMonths[month]} ${year}`;

    const grid = document.getElementById("ag-cal-grid");
    grid.innerHTML = "";

    const firstDay = new Date(year, month, 1).getDay();
    const totalDays = new Date(year, month + 1, 0).getDate();

    document.getElementById("total_absensi").innerText = totalDays;

    // offset kosong
    for (let i = 0; i < firstDay; i++) {
        grid.innerHTML += `<div class="ag-cal-day muted"></div>`;
    }

    // tanggal
    for (let d = 1; d <= totalDays; d++) {
        let cls = "ag-cal-day";

        const dateKey = `${year}-${String(month + 1).padStart(2, "0")}-${String(d).padStart(2, "0")}`;

        if (
            d === today.getDate() &&
            month === today.getMonth() &&
            year === today.getFullYear()
        ) {
            cls += " today";
        }

        const izin = isIzin(dateKey);
        const lembur = getLembur(dateKey);

        if (agEvent[dateKey]) cls += " has-dot";
        if (izin) cls += " izin";
        if (lembur) cls += " lembur";

        // ✅ tanggal merah dari API
        if (agLibur[dateKey]) {
            cls += " holiday";
        }

        grid.innerHTML += `
            <div class="${cls}" data-date="${dateKey}" title="${agLibur[dateKey] || ""}">
                ${d}
            </div>
        `;
    }

    // ── CLICK EVENT ────────────────────
    document.querySelectorAll(".ag-cal-day").forEach((el) => {
        el.onclick = function () {
            const date = this.dataset.date;
            const list = agEvent[date];
            const izin = isIzin(date);
            const lembur = getLembur(date);

            if (
                (!list || list.length === 0) &&
                !agLibur[date] &&
                !izin &&
                !lembur
            )
                return;

            const badgeMap = {
                Terlambat: "text-bg-danger",
                Hadir: "text-bg-success",
                Libur: "text-bg-danger",
                Cuti: "text-bg-warning",
                Lembur: "text-bg-info",
            };

            document.getElementById("agDetailTanggal").innerText =
                formatTanggalIndo(date);

            // RESET dulu
            document.getElementById("agDetailMasuk").innerText = "-";
            document.getElementById("agDetailKeluar").innerText = "-";

            // PRIORITAS: Libur → Izin → Absen → Lembur

            if (agLibur[date]) {
                document.getElementById("agDetailStatus").className =
                    "badge " + badgeMap["Libur"];

                document.getElementById("agDetailStatus").innerText = "Libur";

                document.getElementById("agDetailMasuk").innerText =
                    "Alasan Tanggal Merah: \t" + agLibur[date];
                document.getElementById("agDetailKeluar").innerText = "";
            } else if (izin) {
                document.getElementById("agDetailStatus").className =
                    "badge " + badgeMap["Cuti"];

                document.getElementById("agDetailStatus").innerText = "Cuti";

                document.getElementById("agDetailMasuk").innerText =
                    "Alasan Izin: \t\t\t" + izin.alasan || "-";

                document.getElementById("agDetailKeluar").innerText = "";
            } else if (list) {
                const masuk = list[0];
                const keluar = list[list.length - 1];

                document.getElementById("agDetailStatus").className =
                    "badge " + badgeMap[masuk.status];

                document.getElementById("agDetailStatus").innerText =
                    masuk.status;

                document
                    .getElementById("agDetailMasuk")
                    .classList.add("text-center");

                document.getElementById("agDetailMasuk").innerText =
                    "Jam Masuk: \t\t\t" +
                    masuk.jam_masuk +
                    " | Jam Keluar: \t\t\t" +
                    masuk.jam_keluar;

                document.getElementById("agDetailKeluar").innerText = "";
            }

            // ✅ LEMBUR (DITAMBAH, bukan else!)
            if (lembur) {
                document.getElementById("agDetailStatus").className =
                    "badge " + badgeMap["Lembur"];

                document.getElementById("agDetailStatus").innerText = "Lembur";

                document.getElementById("agDetailMasuk").innerText =
                    "Jam Masuk: \t\t\t" +
                    `${lembur.jam_mulai || "-"} | Jam Keluar: \t\t\t` +
                    `${lembur.jam_selesai || "-"}`;

                document.getElementById("agDetailKeluar").innerText = "";
            }

            new bootstrap.Modal(
                document.getElementById("agDetailModal"),
            ).show();
        };
    });
}

// ── CHANGE MONTH ───────────────────────
function agChangeMonth(dir) {
    const oldYear = agCur.getFullYear();

    agCur.setMonth(agCur.getMonth() + dir);

    const newYear = agCur.getFullYear();

    if (oldYear !== newYear) {
        agFetchLibur(newYear);
    } else {
        agRenderCal();
    }
}

// ── INIT ───────────────────────────────
agFetchLibur(agCur.getFullYear());
agRenderCal();
