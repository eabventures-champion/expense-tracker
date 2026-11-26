<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
 <Worksheet ss:Name="CediLedger Report">
  <Table>
   <Row>
    <Cell><Data ss:Type="String">CEDILEDGER PERSONAL FINANCIAL REPORT ({{ $startDate }} to {{ $endDate }})</Data></Cell>
   </Row>
   <Row>
    <Cell><Data ss:Type="String">Total Income</Data></Cell>
    <Cell><Data ss:Type="Number">{{ $totalIncome }}</Data></Cell>
    <Cell><Data ss:Type="String">Total Expenses</Data></Cell>
    <Cell><Data ss:Type="Number">{{ $totalExpenses }}</Data></Cell>
    <Cell><Data ss:Type="String">Net Cash Flow</Data></Cell>
    <Cell><Data ss:Type="Number">{{ $netBalance }}</Data></Cell>
   </Row>
   <Row>
    <Cell><Data ss:Type="String">Date</Data></Cell>
    <Cell><Data ss:Type="String">Reference</Data></Cell>
    <Cell><Data ss:Type="String">Title</Data></Cell>
    <Cell><Data ss:Type="String">Type</Data></Cell>
    <Cell><Data ss:Type="String">Category</Data></Cell>
    <Cell><Data ss:Type="String">Subcategory</Data></Cell>
    <Cell><Data ss:Type="String">Payment Method</Data></Cell>
    <Cell><Data ss:Type="String">Amount ({{ $user->currency }})</Data></Cell>
   </Row>
   @foreach($transactions as $tx)
   <Row>
    <Cell><Data ss:Type="String">{{ $tx->transaction_date->format('Y-m-d') }}</Data></Cell>
    <Cell><Data ss:Type="String">{{ $tx->reference_code }}</Data></Cell>
    <Cell><Data ss:Type="String">{{ $tx->title }}</Data></Cell>
    <Cell><Data ss:Type="String">{{ strtoupper($tx->type) }}</Data></Cell>
    <Cell><Data ss:Type="String">{{ $tx->category?->name ?? 'General' }}</Data></Cell>
    <Cell><Data ss:Type="String">{{ $tx->subcategory?->name ?? '' }}</Data></Cell>
    <Cell><Data ss:Type="String">{{ $tx->paymentMethod?->name ?? '' }}</Data></Cell>
    <Cell><Data ss:Type="Number">{{ number_format((float)$tx->amount, 2, '.', '') }}</Data></Cell>
   </Row>
   @endforeach
  </Table>
 </Worksheet>
</Workbook>
