<table>
    <thead>
        <tr>
            <th>product_code</th>
            <th>product_type</th>
            <th>product_name</th>
            <th>product_price</th>
            <th>product_active</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($products as $product)
            <tr>
                <td>{{ $product->product_code }}</td>
                <td>{{ $product->product_type }}</td>
                <td>{{ $product->product_name }}</td>
                <td>{{ $product->product_price }}</td>
                <td>{{ $product->product_active }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5">ไม่มีข้อมูลสินค้า</td>
            </tr>
        @endforelse
    </tbody>
</table>
