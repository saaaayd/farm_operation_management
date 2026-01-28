import jsPDF from 'jspdf'
import 'jspdf-autotable'

/**
 * PDF Export Utility for Farm Reports
 * Uses jsPDF for client-side PDF generation
 */
export const pdfExport = {
    /**
     * Export Financial Report to PDF
     */
    exportFinancialReport(data, options = {}) {
        const doc = new jsPDF()
        const { title = 'Financial Report', period = 'All Time' } = options

        // Header
        doc.setFontSize(20)
        doc.setTextColor(22, 101, 52) // Green
        doc.text('ANIBUKID', 14, 20)

        doc.setFontSize(16)
        doc.setTextColor(0, 0, 0)
        doc.text(title, 14, 30)

        doc.setFontSize(10)
        doc.setTextColor(100, 100, 100)
        doc.text(`Period: ${period}`, 14, 38)
        doc.text(`Generated: ${new Date().toLocaleDateString()}`, 14, 44)

        // Summary Section
        let yPos = 55
        doc.setFontSize(12)
        doc.setTextColor(0, 0, 0)
        doc.text('Summary', 14, yPos)

        yPos += 8
        doc.setFontSize(10)
        doc.text(`Total Revenue: ₱${(data.totalRevenue || 0).toLocaleString()}`, 20, yPos)
        yPos += 6
        doc.text(`Total Expenses: ₱${(data.totalExpenses || 0).toLocaleString()}`, 20, yPos)
        yPos += 6
        doc.text(`Net Profit: ₱${(data.netProfit || 0).toLocaleString()}`, 20, yPos)

        // Expenses by Category Table
        if (data.expensesByCategory && data.expensesByCategory.length > 0) {
            yPos += 15
            doc.setFontSize(12)
            doc.text('Expenses by Category', 14, yPos)

            doc.autoTable({
                startY: yPos + 5,
                head: [['Category', 'Amount (₱)', '% of Total']],
                body: data.expensesByCategory.map(item => [
                    item.category || 'Other',
                    (item.amount || 0).toLocaleString(),
                    `${(item.percentage || 0).toFixed(1)}%`
                ]),
                theme: 'striped',
                headStyles: { fillColor: [22, 101, 52] }
            })
        }

        // Save
        doc.save(`${title.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.pdf`)
    },

    /**
     * Export Crop Yield Report to PDF
     */
    exportCropYieldReport(data, options = {}) {
        const doc = new jsPDF()
        const { title = 'Crop Yield Report' } = options

        // Header
        doc.setFontSize(20)
        doc.setTextColor(22, 101, 52)
        doc.text('ANIBUKID', 14, 20)

        doc.setFontSize(16)
        doc.setTextColor(0, 0, 0)
        doc.text(title, 14, 30)

        doc.setFontSize(10)
        doc.setTextColor(100, 100, 100)
        doc.text(`Generated: ${new Date().toLocaleDateString()}`, 14, 38)

        // Summary
        let yPos = 50
        doc.setFontSize(12)
        doc.setTextColor(0, 0, 0)
        doc.text('Harvest Summary', 14, yPos)

        yPos += 8
        doc.setFontSize(10)
        doc.text(`Total Harvests: ${data.totalHarvests || 0}`, 20, yPos)
        yPos += 6
        doc.text(`Total Yield: ${(data.totalYield || 0).toLocaleString()} kg`, 20, yPos)
        yPos += 6
        doc.text(`Average Yield per Hectare: ${(data.avgYieldPerHa || 0).toFixed(2)} kg/ha`, 20, yPos)

        // Harvests Table
        if (data.harvests && data.harvests.length > 0) {
            yPos += 15
            doc.setFontSize(12)
            doc.text('Harvest Records', 14, yPos)

            doc.autoTable({
                startY: yPos + 5,
                head: [['Date', 'Field', 'Variety', 'Yield (kg)', 'Quality']],
                body: data.harvests.map(h => [
                    h.harvest_date || 'N/A',
                    h.field_name || 'N/A',
                    h.variety_name || 'N/A',
                    (h.yield || h.quantity || 0).toLocaleString(),
                    h.quality_grade || 'N/A'
                ]),
                theme: 'striped',
                headStyles: { fillColor: [22, 101, 52] }
            })
        }

        doc.save(`${title.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.pdf`)
    },

    /**
     * Export Weather Report to PDF
     */
    exportWeatherReport(data, options = {}) {
        const doc = new jsPDF()
        const { title = 'Weather Report', fieldName = 'All Fields' } = options

        // Header
        doc.setFontSize(20)
        doc.setTextColor(22, 101, 52)
        doc.text('ANIBUKID', 14, 20)

        doc.setFontSize(16)
        doc.setTextColor(0, 0, 0)
        doc.text(title, 14, 30)

        doc.setFontSize(10)
        doc.setTextColor(100, 100, 100)
        doc.text(`Field: ${fieldName}`, 14, 38)
        doc.text(`Generated: ${new Date().toLocaleDateString()}`, 14, 44)

        // Current Conditions
        if (data.current) {
            let yPos = 55
            doc.setFontSize(12)
            doc.setTextColor(0, 0, 0)
            doc.text('Current Conditions', 14, yPos)

            yPos += 8
            doc.setFontSize(10)
            doc.text(`Temperature: ${data.current.temperature || 'N/A'}°C`, 20, yPos)
            yPos += 6
            doc.text(`Humidity: ${data.current.humidity || 'N/A'}%`, 20, yPos)
            yPos += 6
            doc.text(`Wind Speed: ${data.current.wind_speed || 'N/A'} km/h`, 20, yPos)
            yPos += 6
            doc.text(`Conditions: ${data.current.conditions || 'N/A'}`, 20, yPos)
        }

        // GDD Summary
        if (data.gdd) {
            let yPos = doc.lastAutoTable?.finalY || 90
            yPos += 15
            doc.setFontSize(12)
            doc.text('Growing Degree Days (GDD)', 14, yPos)

            yPos += 8
            doc.setFontSize(10)
            doc.text(`Total GDD: ${(data.gdd.total || 0).toFixed(1)}`, 20, yPos)
            yPos += 6
            doc.text(`7-Day Avg: ${(data.gdd.weekly_avg || 0).toFixed(1)}`, 20, yPos)
        }

        doc.save(`${title.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.pdf`)
    },

    /**
     * Generic table export
     */
    exportTable(tableData, options = {}) {
        const doc = new jsPDF()
        const { title = 'Data Export', headers = [], rows = [] } = options

        doc.setFontSize(16)
        doc.text(title, 14, 20)
        doc.setFontSize(10)
        doc.text(`Generated: ${new Date().toLocaleDateString()}`, 14, 28)

        if (headers.length > 0 && rows.length > 0) {
            doc.autoTable({
                startY: 35,
                head: [headers],
                body: rows,
                theme: 'striped',
                headStyles: { fillColor: [22, 101, 52] }
            })
        }

        doc.save(`${title.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.pdf`)
    }
}

export default pdfExport
