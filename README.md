# CargoTrace - Global Shipment Visibility & Tracking Platform

A full-stack logistics management platform built with Laravel for tracking ocean freight container shipments, handling delay exceptions, visualizing global routes on interactive maps, and integrating real-time weather and currency exchange APIs.

---

## 📌 Project Overview
CargoTrace resolves critical challenges in ocean freight logistics by centralizing scattered shipment data, providing interactive timeline tracking, calculating ETA delay thresholds, and managing port operational exceptions.

---

## 🚀 Key Features
- **Operational Dashboard**: Real-time KPI metrics, status breakdown charts (Chart.js), and live multi-currency rates (USD base).
- **Interactive Map**: Route line rendering and port markers powered by Leaflet.js & OpenStreetMap.
- **Shipment Lifecycle Management**: Validated status transitions (`CREATED` → `BOOKED` → `PICKED_UP` → `IN_TRANSIT` → `DELIVERED`).
- **Waybill & Resi Tracking**: Chronological tracking event timelines for individual shipments.
- **Exception & Delay Rules**: Threshold-based delay detection (12-hour threshold rule).
- **Live Weather API**: Real-time temperature & condition integration for global ports (Open-Meteo API).
- **Role-Based Access Control (RBAC)**: Distinct permissions for Administrators, Logistics Operators, and Customers.
- **RESTful API**: JSON endpoints for external integration.

---

## 🛠️ Tech Stack
- **Framework**: Laravel 10 / 11
- **Backend Language**: PHP 8.2+
- **Database**: MySQL / MariaDB
- **Frontend**: Blade Templates, Tailwind CSS, JavaScript (Vanilla ES6)
- **Mapping & Charts**: Leaflet.js, Chart.js
- **APIs**: Open-Meteo Weather API, Open Exchange Rates API

---

## 💻 Installation & Setup

1. **Clone Repository**
   ```bash
   git clone [https://github.com/saimah14/cargotrace.git](https://github.com/saimah14/cargotrace.git)
   cd cargotrace